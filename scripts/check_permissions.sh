#!/bin/bash

# Script de verificación de permisos de archivos para ISO 27001 Platform
# Ejecutar: bash scripts/check_permissions.sh

echo "=== Verificación de Permisos de Archivos ==="
echo ""

PROJECT_ROOT="/var/www/html"
ERRORS=0

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

check_permission() {
    local file=$1
    local expected=$2
    local current=$(stat -c "%a" "$file" 2>/dev/null)
    
    if [ -z "$current" ]; then
        echo -e "${RED}[ERROR]${NC} Archivo no existe: $file"
        ((ERRORS++))
        return
    fi
    
    if [ "$current" != "$expected" ]; then
        echo -e "${YELLOW}[WARN]${NC} $file tiene permisos $current (esperado: $expected)"
        ((ERRORS++))
    else
        echo -e "${GREEN}[OK]${NC} $file ($current)"
    fi
}

check_owner() {
    local file=$1
    local expected_user=$2
    local current_owner=$(stat -c "%U" "$file" 2>/dev/null)
    
    if [ "$current_owner" != "$expected_user" ]; then
        echo -e "${YELLOW}[WARN]${NC} $file owner: $current_owner (esperado: $expected_user)"
        ((ERRORS++))
    fi
}

echo "Verificando archivos de código..."
check_permission "$PROJECT_ROOT/public/index.php" "644"
check_permission "$PROJECT_ROOT/composer.json" "644"
check_permission "$PROJECT_ROOT/.env" "600"

echo ""
echo "Verificando directorios de almacenamiento..."
check_permission "$PROJECT_ROOT/storage/logs" "755"
check_permission "$PROJECT_ROOT/storage/cache" "755"
check_permission "$PROJECT_ROOT/storage/sessions" "755"
check_permission "$PROJECT_ROOT/public/uploads" "755"

echo ""
echo "Verificando ownership (debe ser www-data)..."
check_owner "$PROJECT_ROOT/storage/logs" "www-data"
check_owner "$PROJECT_ROOT/storage/cache" "www-data"
check_owner "$PROJECT_ROOT/storage/sessions" "www-data"
check_owner "$PROJECT_ROOT/public/uploads" "www-data"

echo ""
echo "Verificando archivos sensibles..."
if [ -f "$PROJECT_ROOT/.env" ]; then
    check_permission "$PROJECT_ROOT/.env" "600"
else
    echo -e "${RED}[ERROR]${NC} Archivo .env no existe"
    ((ERRORS++))
fi

echo ""
echo "=== Resumen ==="
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}Todos los permisos están correctos${NC}"
    exit 0
else
    echo -e "${RED}Se encontraron $ERRORS advertencias/errores${NC}"
    echo ""
    echo "Para corregir permisos ejecutar:"
    echo "  sudo chown -R www-data:www-data $PROJECT_ROOT/storage"
    echo "  sudo chown -R www-data:www-data $PROJECT_ROOT/public/uploads"
    echo "  sudo chmod 600 $PROJECT_ROOT/.env"
    echo "  sudo chmod 644 $PROJECT_ROOT/public/index.php"
    echo "  sudo chmod 755 $PROJECT_ROOT/storage/logs"
    echo "  sudo chmod 755 $PROJECT_ROOT/storage/cache"
    echo "  sudo chmod 755 $PROJECT_ROOT/storage/sessions"
    echo "  sudo chmod 755 $PROJECT_ROOT/public/uploads"
    exit 1
fi
