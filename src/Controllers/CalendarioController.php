<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Core\Validator;
use App\Core\Database;
use App\Services\AuditService;
use App\Middleware\RoleMiddleware;
use PDO;

class CalendarioController extends Controller
{
    private AuditService $auditService;
    private PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->auditService = new AuditService();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Vista principal del calendario
     */
    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $this->view('calendario/index');
    }

    /**
     * Obtiene eventos del calendario
     */
    public function getEventos(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        $mes = $request->get('mes') ?? date('m');
        $anio = $request->get('anio') ?? date('Y');

        // Auditorías programadas
        $sql = "SELECT id, titulo, descripcion, tipo, fecha_inicio, fecha_fin, estado, auditor_responsable
                FROM auditorias_programadas
                WHERE empresa_id = :empresa_id
                AND deleted_at IS NULL
                AND (MONTH(fecha_inicio) = :mes AND YEAR(fecha_inicio) = :anio
                     OR MONTH(fecha_fin) = :mes2 AND YEAR(fecha_fin) = :anio2)
                ORDER BY fecha_inicio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        $stmt->bindValue(':mes2', $mes, PDO::PARAM_INT);
        $stmt->bindValue(':anio2', $anio, PDO::PARAM_INT);
        $stmt->execute();
        $auditorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Acciones con fecha compromiso este mes
        $sqlAcciones = "SELECT a.id, a.descripcion as titulo, a.fecha_compromiso as fecha_inicio,
                               a.estado, a.responsable, c.codigo as control_codigo
                        FROM acciones a
                        INNER JOIN gap_items g ON a.gap_id = g.id
                        INNER JOIN soa_entries s ON g.soa_id = s.id
                        INNER JOIN controles c ON s.control_id = c.id
                        WHERE g.empresa_id = :empresa_id
                        AND a.estado_accion = 'activo'
                        AND MONTH(a.fecha_compromiso) = :mes AND YEAR(a.fecha_compromiso) = :anio
                        ORDER BY a.fecha_compromiso";
        
        $stmt = $this->db->prepare($sqlAcciones);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        $stmt->execute();
        $acciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $eventos = [];

        // Formatear auditorías
        foreach ($auditorias as $a) {
            $eventos[] = [
                'id' => 'auditoria_' . $a['id'],
                'tipo' => 'auditoria',
                'titulo' => $a['titulo'],
                'descripcion' => $a['descripcion'],
                'fecha_inicio' => $a['fecha_inicio'],
                'fecha_fin' => $a['fecha_fin'],
                'estado' => $a['estado'],
                'tipo_auditoria' => $a['tipo'],
                'responsable' => $a['auditor_responsable'],
                'color' => $this->getColorAuditoria($a['tipo'])
            ];
        }

        // Formatear acciones
        foreach ($acciones as $a) {
            $eventos[] = [
                'id' => 'accion_' . $a['id'],
                'tipo' => 'accion',
                'titulo' => "[{$a['control_codigo']}] " . substr($a['titulo'], 0, 50),
                'fecha_inicio' => $a['fecha_inicio'],
                'fecha_fin' => null,
                'estado' => $a['estado'],
                'responsable' => $a['responsable'],
                'color' => $this->getColorAccion($a['estado'])
            ];
        }

        $this->json([
            'success' => true,
            'data' => $eventos,
            'mes' => $mes,
            'anio' => $anio
        ]);
    }

    /**
     * Crea una nueva auditoría programada
     */
    public function crearAuditoria(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('audit.edit')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        $userId = $this->user()['id'];

        $validator = new Validator($request->all());
        $rules = [
            'titulo' => 'required|min:3|max:255',
            'tipo' => 'required',
            'fecha_inicio' => 'required'
        ];

        if (!$validator->validate($rules)) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        try {
            $sql = "INSERT INTO auditorias_programadas 
                    (empresa_id, titulo, descripcion, tipo, auditor_responsable, 
                     fecha_inicio, fecha_fin, estado, controles_alcance, created_by)
                    VALUES (:empresa_id, :titulo, :descripcion, :tipo, :auditor,
                            :fecha_inicio, :fecha_fin, 'programada', :controles, :created_by)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':empresa_id' => $empresaId,
                ':titulo' => $request->post('titulo'),
                ':descripcion' => $request->post('descripcion'),
                ':tipo' => $request->post('tipo'),
                ':auditor' => $request->post('auditor_responsable'),
                ':fecha_inicio' => $request->post('fecha_inicio'),
                ':fecha_fin' => $request->post('fecha_fin') ?: null,
                ':controles' => $request->post('controles_alcance'),
                ':created_by' => $userId
            ]);

            $auditoriaId = $this->db->lastInsertId();

            $this->auditService->log('INSERT', 'auditorias_programadas', $auditoriaId, null, [
                'titulo' => $request->post('titulo'),
                'tipo' => $request->post('tipo'),
                'fecha_inicio' => $request->post('fecha_inicio')
            ]);

            $this->json(['success' => true, 'message' => 'Auditoría programada', 'id' => $auditoriaId]);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => 'Error al crear: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza estado de una auditoría
     */
    public function actualizarAuditoria(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('audit.edit')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];

        try {
            $campos = [];
            $params = [':id' => (int)$id, ':empresa_id' => $empresaId];

            $camposPermitidos = ['titulo', 'descripcion', 'tipo', 'auditor_responsable', 
                                'fecha_inicio', 'fecha_fin', 'estado', 'hallazgos', 'conclusiones'];

            foreach ($camposPermitidos as $campo) {
                if ($request->post($campo) !== null) {
                    $campos[] = "{$campo} = :{$campo}";
                    $params[":{$campo}"] = $request->post($campo);
                }
            }

            if (empty($campos)) {
                $this->json(['success' => false, 'error' => 'No hay datos para actualizar'], 400);
                return;
            }

            $sql = "UPDATE auditorias_programadas SET " . implode(', ', $campos) . 
                   " WHERE id = :id AND empresa_id = :empresa_id AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $this->auditService->log('UPDATE', 'auditorias_programadas', (int)$id, [], $params);
                $this->json(['success' => true, 'message' => 'Auditoría actualizada']);
            } else {
                $this->json(['success' => false, 'error' => 'Auditoría no encontrada'], 404);
            }

        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene detalle de una auditoría
     */
    public function getAuditoria(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];

        $sql = "SELECT a.*, u.nombre as creado_por_nombre
                FROM auditorias_programadas a
                INNER JOIN usuarios u ON a.created_by = u.id
                WHERE a.id = :id AND a.empresa_id = :empresa_id AND a.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id, ':empresa_id' => $empresaId]);
        $auditoria = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($auditoria) {
            $this->json(['success' => true, 'data' => $auditoria]);
        } else {
            $this->json(['success' => false, 'error' => 'No encontrada'], 404);
        }
    }

    private function getColorAuditoria(string $tipo): string
    {
        return match($tipo) {
            'interna' => '#3498db',
            'externa' => '#e74c3c',
            'seguimiento' => '#f39c12',
            'certificacion' => '#27ae60',
            default => '#95a5a6'
        };
    }

    private function getColorAccion(string $estado): string
    {
        return match($estado) {
            'pendiente' => '#e74c3c',
            'en_proceso' => '#f39c12',
            'completada' => '#27ae60',
            default => '#95a5a6'
        };
    }
}
