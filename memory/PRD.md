# ISO 27001 Compliance Platform - PRD

## Original Problem Statement
Plataforma PHP para gestión de cumplimiento ISO 27001. El proyecto inició con la corrección de un bug de reset de contraseña y se expandió a un desarrollo completo de múltiples módulos.

## User Preferences
- **Idioma**: Español
- **Rechazado**: API REST, 2FA (por ahora)
- **Enfoque**: Mejoras de seguridad y funcionalidad core, sin cambios UI/UX

## Tech Stack
- **Backend**: PHP puro (sin framework), patrón MVC
- **Database**: MySQL
- **Deployment**: Manual via git pull en servidor del usuario

## Modules Status

### ✅ Completed
- **Módulo 1**: Políticas de contraseña (historial, complejidad, expiración 90 días)
- **Módulo 4**: Notificaciones (servicio, panel, script cron)
- **Módulo 5**: Reportes (SOA, GAPs, Ejecutivo en HTML/CSV)
- **Módulo 6**: Workflows (historial de estados de evidencias)
- **Módulo 7 (parte 1)**: Calendario de auditorías

### 🔄 In Progress
- Bug fixes para Módulos 4, 5 (COMPLETADO 2024-12-XX)

### 📋 Pending
- **Módulo 2**: Auditoría mejorada (IP, user-agent, filtros avanzados)
- **Módulo 3**: Búsqueda global en todos los módulos
- **Módulo 7 (parte 2)**: Versionado de evidencias

### ❌ Rejected/Deferred
- API REST
- Autenticación 2FA

## Key Files Modified (Latest Session)
- `src/Middleware/RoleMiddleware.php` - Permisos para nuevos módulos
- `src/Controllers/NotificacionController.php` - Permisos corregidos
- `src/Services/ReporteService.php` - Nullable types PHP 8.x

## Database Tables (New)
- `password_history` - Historial de contraseñas
- `evidencia_historial` - Log de estados de evidencias
- `auditorias_programadas` - Calendario de auditorías
- `evidencia_versiones` - (estructura creada, lógica pendiente)

## Known Issues
- Permisos de archivos en servidor del usuario (neo vs www-data)
- Requiere comandos específicos para git pull
