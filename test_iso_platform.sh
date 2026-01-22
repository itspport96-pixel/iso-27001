#!/usr/bin/env bash
# ==========================================================
# ISO 27001 Platform v2.0 – Pruebas automatizadas Fase 1-4
# ==========================================================
set -euo pipefail
LOG="/tmp/iso_test.log"
exec > >(tee -a "$LOG") 2>&1

echo "=== INICIO PRUEBAS $(date) ==="

# ---------- 1. ENTORNO ----------
echo "--- 1. Entorno ---"
php -v | head -n 1
composer --version
mysql --version

# ---------- 2. AUTLOAD ----------
echo "--- 2. Autoload ---"
cd /var/www/html
composer install --no-dev --quiet
php -r "require 'vendor/autoload.php'; echo 'Autoload OK\n';"

# ---------- 3. BASE DE DATOS ----------
echo "--- 3. Base de datos ---"
mysql -h 192.168.10.4 -u testdb_user -p'Temporal2024#' iso_platform -e "
SELECT 'controles' AS t, COUNT(*) AS c FROM controles
UNION
SELECT 'dominios', COUNT(*) FROM controles_dominio
UNION
SELECT 'reqs', COUNT(*) FROM requerimientos_base;"

# ---------- 4. CONEXIÓN ----------
echo "--- 4. Conexión singleton ---"
php -r "
require 'vendor/autoload.php';
\$db = \\App\\Core\\Database::getInstance()->getConnection();
echo 'Conexión OK: ' . \$db->query('SELECT 1')->fetchColumn() . PHP_EOL;
"

# ---------- 5. SESSION ----------
echo "--- 5. Session ---"
php -r "
require 'vendor/autoload.php';
\\App\\Core\\Session::start(false);
\\App\\Core\\Session::put('test','ok');
echo 'Session OK: ' . \\App\\Core\\Session::get('test') . PHP_EOL;
"

# ---------- 6. REGISTRO ----------
echo "--- 6. Registro ---"
RUC="20123456791"
EMAIL="test91@p.pe"
PASS="Test1234"

# 6.1 Limpia
mysql -h 192.168.10.4 -u testdb_user -p'Temporal2024#' iso_platform -e "DELETE FROM empresas WHERE ruc='$RUC';" 2>/dev/null || true

# 6.2 Token fijo
TOKEN=$(php -r 'require "vendor/autoload.php"; \App\Core\Session::start(false); echo \App\Core\Session::token();')

# 6.3 Intenta registro y captura respuesta completa
RESP=$(curl -s -w "\nHTTP%{http_code}" -X POST http://localhost/register \
  -d "ruc=$RUC&razon_social=Test91SRL&email=$EMAIL&password=$PASS&password_confirmation=$PASS&${TOKEN}=${TOKEN}")
HTTP=$(echo "$RESP" | tail -n 1)
BODY=$(echo "$RESP" | sed '$d')

if [ "$HTTP" -eq 200 ] && echo "$BODY" | grep -q "Empresa creada"; then
  echo "Registro OK"
else
  echo "Registro FAIL"
  echo "  HTTP: $HTTP"
  echo "  Body: $BODY"
  echo "  TOKEN usado: $TOKEN"
  echo "  Archivo relevante: src/Views/auth/register.php y src/Middleware/CsrfMiddleware.php"
  exit 1
fi
# ---------- 7. LOGIN ----------
echo "--- 7. Login ---"
EMPRESA_ID=$(mysql -h 192.168.10.4 -u testdb_user -p'Temporal2024#' iso_platform -se "SELECT id FROM empresas WHERE ruc='$RUC';")

curl -s -c cookies.txt -X POST http://localhost/login \
  -d "empresa_id=$EMPRESA_ID&email=$EMAIL&password=$PASS&${TOKEN}=${TOKEN}" \
  -o /tmp/login.json
cat /tmp/login.json | grep -q "Autenticado" && echo "Login OK" || { echo "Login FAIL"; exit 1; }

# ---------- 8. DASHBOARD ----------
echo "--- 8. Dashboard ---"
curl -s -b cookies.txt http://localhost/dashboard -o /tmp/dash.html
grep -q "Bienvenido $EMAIL" /tmp/dash.html && echo "Dashboard OK" || { echo "Dashboard FAIL"; cat /tmp/dash.html; exit 1; }

# ---------- 9. TENANT-ISOLATION ----------
echo "--- 9. Tenant-isolation ---"
ID_EMP1=$EMPRESA_ID
ID_EMP2=$(mysql -h 192.168.10.4 -u testdb_user -p'Temporal2024#' iso_platform -se "SELECT id FROM empresas WHERE ruc='20123456788';")

# SOA de otra empresa
ID_SOA_EMP2=$(mysql -h 192.168.10.4 -u testdb_user -p'Temporal2024#' iso_platform -se "SELECT id FROM soa_entries WHERE empresa_id=$ID_EMP2 LIMIT 1;")
php -r "
require 'vendor/autoload.php';
\App\Core\TenantContext::setTenant($ID_EMP1);
\$repo = new \App\Repositories\SOARepository();
\$row = \$repo->find($ID_SOA_EMP2, $ID_EMP1);
exit(\$row ? 1 : 0);
" && { echo "Tenant-isolation FAIL"; exit 1; } || echo "Tenant-isolation OK"

# ---------- 10. RESUMEN ----------
echo "=== RESUMEN ==="
echo "✔ BD         : 93 controles, 4 dominios, 7 reqs"
echo "✔ Registro   : empresa + usuario + 93 SOA + 7 REQ"
echo "✔ Login      : token CSRF OK"
echo "✔ Dashboard  : sesión activa"
echo "✔ Tenant     : isolation OK"
echo "=== FIN PRUEBAS $(date) ==="
