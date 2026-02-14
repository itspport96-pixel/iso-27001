<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\TenantContext;
use App\Middleware\RoleMiddleware;
use App\Services\ConfiguracionService;
use App\Services\MailService;
use App\Services\AuditService;

class ConfiguracionController extends Controller
{
    private AuditService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->auditService = new AuditService();
    }

    private function getServicio(): ConfiguracionService
    {
        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);
        return new ConfiguracionService($empresaId);
    }

    private function checkAcceso(): bool
    {
        $this->requireAuth();
        if (!RoleMiddleware::can('configuracion.manage')) {
            $this->response->error('Acceso denegado', 403);
            return false;
        }
        return true;
    }

    public function index(Request $request, Response $response): void
    {
        $this->request  = $request;
        $this->response = $response;

        if (!$this->checkAcceso()) {
            return;
        }

        $servicio = $this->getServicio();
        $empresa  = $servicio->getEmpresa();
        $smtp     = $servicio->getSmtp();

        $smtpTienePwd = !empty($smtp['smtp_password']);
        $smtp['smtp_password'] = '';

        $this->view('configuracion/index', [
            'empresa'        => $empresa,
            'smtp'           => $smtp,
            'smtp_tiene_pwd' => $smtpTienePwd,
            'tab'            => $request->get('tab', 'empresa'),
            'success'        => $this->session->getFlash('success'),
            'error'          => $this->session->getFlash('error'),
        ]);
    }

    public function saveEmpresa(Request $request, Response $response): void
    {
        $this->request  = $request;
        $this->response = $response;

        if (!$this->checkAcceso()) {
            return;
        }

        $validator = new Validator($request->all());
        $rules = [
            'nombre'   => 'required|min:3|max:255',
            'email'    => 'required|email',
            'telefono' => 'max:20',
        ];

        if (!$validator->validate($rules)) {
            $this->session->flash('error', implode(' ', array_merge(...array_values($validator->errors()))));
            $this->redirect('/configuracion?tab=empresa');
            return;
        }

        $servicio = $this->getServicio();

        $datos = [
            'nombre'    => $request->post('nombre'),
            'contacto'  => $request->post('contacto'),
            'telefono'  => $request->post('telefono'),
            'email'     => $request->post('email'),
            'direccion' => $request->post('direccion'),
            'sector'    => $request->post('sector'),
        ];

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoResult = $this->procesarLogo($_FILES['logo'], $this->user()['empresa_id']);
            if ($logoResult['success']) {
                $servicio->saveLogo($logoResult['path']);
            } else {
                $this->session->flash('error', $logoResult['error']);
                $this->redirect('/configuracion?tab=empresa');
                return;
            }
        }

        if ($servicio->saveEmpresa($datos)) {
            $this->auditService->log('UPDATE', 'empresas', $this->user()['empresa_id'], null, $datos);
            $this->session->flash('success', 'Datos de empresa actualizados correctamente.');
        } else {
            $this->session->flash('error', 'Error al guardar los datos de empresa.');
        }

        $this->redirect('/configuracion?tab=empresa');
    }

    public function saveSmtp(Request $request, Response $response): void
    {
        $this->request  = $request;
        $this->response = $response;

        if (!$this->checkAcceso()) {
            return;
        }

        $validator = new Validator($request->all());
        $rules = [
            'smtp_host'       => 'required|max:255',
            'smtp_port'       => 'required|numeric',
            'smtp_usuario'    => 'required|max:255',
            'smtp_from_email' => 'required|email',
        ];

        if (!$validator->validate($rules)) {
            $this->session->flash('error', implode(' ', array_merge(...array_values($validator->errors()))));
            $this->redirect('/configuracion?tab=smtp');
            return;
        }

        $datos = [
            'smtp_host'        => $request->post('smtp_host'),
            'smtp_port'        => $request->post('smtp_port'),
            'smtp_usuario'     => $request->post('smtp_usuario'),
            'smtp_password'    => $request->post('smtp_password'),
            'smtp_cifrado'     => $request->post('smtp_cifrado', 'tls'),
            'smtp_from_email'  => $request->post('smtp_from_email'),
            'smtp_from_nombre' => $request->post('smtp_from_nombre'),
            'smtp_activo'      => $request->post('smtp_activo', '0'),
        ];

        $servicio = $this->getServicio();

        if ($servicio->saveSmtp($datos)) {
            $this->auditService->log('UPDATE', 'empresa_configuraciones', $this->user()['empresa_id'], null, [
                'smtp_host'       => $datos['smtp_host'],
                'smtp_port'       => $datos['smtp_port'],
                'smtp_usuario'    => $datos['smtp_usuario'],
                'smtp_from_email' => $datos['smtp_from_email'],
            ]);
            $this->session->flash('success', 'Configuracion SMTP guardada correctamente.');
        } else {
            $this->session->flash('error', 'Error al guardar la configuracion SMTP.');
        }

        $this->redirect('/configuracion?tab=smtp');
    }

    public function testSmtp(Request $request, Response $response): void
    {
        $this->request  = $request;
        $this->response = $response;

        if (!$this->checkAcceso()) {
            return;
        }

        $servicio = $this->getServicio();
        $smtp     = $servicio->getSmtp();

        if (empty($smtp['smtp_host']) || empty($smtp['smtp_usuario'])) {
            $this->json(['success' => false, 'error' => 'Configure primero los datos SMTP antes de probar.']);
            return;
        }

        if (empty($smtp['smtp_activo']) || $smtp['smtp_activo'] !== '1') {
            $this->json(['success' => false, 'error' => 'El SMTP esta deshabilitado. Activelo primero.']);
            return;
        }

        try {
            $mail      = new MailService($smtp);
            $resultado = $mail->testConexion();
            $this->json($resultado);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function procesarLogo(array $file, int $empresaId): array
    {
        $permitidos = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
        $maxSize    = 2 * 1024 * 1024;

        if (!in_array($file['type'], $permitidos, true)) {
            return ['success' => false, 'error' => 'Formato de imagen no permitido. Use PNG, JPG, GIF o WEBP.'];
        }

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'El logo no puede superar 2MB.'];
        }

        $extension       = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombreArchivo   = 'logo_' . $empresaId . '_' . time() . '.' . strtolower($extension);
        $directorioLogos = __DIR__ . '/../../storage/logos/';

        if (!is_dir($directorioLogos)) {
            mkdir($directorioLogos, 0755, true);
        }

        $destino = $directorioLogos . $nombreArchivo;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            return ['success' => false, 'error' => 'Error al guardar el logo en el servidor.'];
        }

        return ['success' => true, 'path' => 'storage/logos/' . $nombreArchivo];
    }
}
