<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Repositories\ConfiguracionRepository;

class ConfiguracionService
{
    private ConfiguracionRepository $repo;
    private int $empresaId;
    private string $encryptionKey;

    private const CIPHER = 'AES-256-CBC';

    public function __construct(int $empresaId)
    {
        $this->repo      = new ConfiguracionRepository();
        $this->empresaId = $empresaId;

        $appKey = $_ENV['APP_KEY'] ?? null;
        if (empty($appKey)) {
            throw new \RuntimeException('APP_KEY no esta configurada en el entorno.');
        }
        $this->encryptionKey = hash('sha256', $appKey, true);
    }

    // -------------------------------------------------------------------------
    // CIFRADO
    // -------------------------------------------------------------------------

    private function encrypt(string $valor): string
    {
        $iv        = random_bytes(openssl_cipher_iv_length(self::CIPHER));
        $cifrado   = openssl_encrypt($valor, self::CIPHER, $this->encryptionKey, 0, $iv);
        return base64_encode($iv . '::' . $cifrado);
    }

    private function decrypt(string $valorCifrado): string
    {
        $decoded = base64_decode($valorCifrado);
        if (strpos($decoded, '::') === false) {
            return '';
        }
        [$iv, $cifrado] = explode('::', $decoded, 2);
        $resultado = openssl_decrypt($cifrado, self::CIPHER, $this->encryptionKey, 0, $iv);
        return $resultado !== false ? $resultado : '';
    }

    // -------------------------------------------------------------------------
    // SMTP
    // -------------------------------------------------------------------------

    /**
     * Devuelve las configuraciones SMTP en texto plano.
     * La password se descifra automaticamente.
     */
    public function getSmtp(): array
    {
        $rows = $this->repo->getByClaves($this->empresaId, Configuracion::SMTP_CLAVES);

        $resultado = array_fill_keys(Configuracion::SMTP_CLAVES, '');

        foreach ($rows as $row) {
            $valor = $row['valor'];
            if ($row['es_cifrado'] && $valor !== null && $valor !== '') {
                $valor = $this->decrypt($valor);
            }
            $resultado[$row['clave']] = $valor ?? '';
        }

        return $resultado;
    }

    /**
     * Guarda las configuraciones SMTP.
     * La password se cifra antes de persistir.
     * Si la password llega vacia, conserva la existente.
     */
    public function saveSmtp(array $datos): bool
    {
        $descripciones = [
            'smtp_host'         => 'Servidor SMTP',
            'smtp_port'         => 'Puerto SMTP',
            'smtp_usuario'      => 'Usuario SMTP',
            'smtp_password'     => 'Contrasena SMTP (cifrada)',
            'smtp_cifrado'      => 'Tipo de cifrado SMTP (tls/ssl/none)',
            'smtp_from_email'   => 'Email remitente',
            'smtp_from_nombre'  => 'Nombre remitente',
            'smtp_activo'       => 'SMTP habilitado (1/0)',
        ];

        foreach (Configuracion::SMTP_CLAVES as $clave) {
            if (!array_key_exists($clave, $datos)) {
                continue;
            }

            $valor = $datos[$clave];

            // Si la password llega vacia, no sobreescribir
            if ($clave === 'smtp_password' && ($valor === null || $valor === '')) {
                continue;
            }

            $esCifrado = in_array($clave, Configuracion::CLAVES_CIFRADAS, true) ? 1 : 0;

            if ($esCifrado && $valor !== null && $valor !== '') {
                $valor = $this->encrypt($valor);
            }

            $ok = $this->repo->set(
                $this->empresaId,
                $clave,
                $valor,
                $esCifrado,
                $descripciones[$clave] ?? ''
            );

            if (!$ok) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // EMPRESA
    // -------------------------------------------------------------------------

    public function getEmpresa(): array
    {
        return $this->repo->getEmpresa($this->empresaId) ?? [];
    }

    public function saveEmpresa(array $datos): bool
    {
        return $this->repo->updateEmpresa($this->empresaId, $datos);
    }

    public function saveLogo(string $logoPath): bool
    {
        return $this->repo->updateLogo($this->empresaId, $logoPath);
    }
}
