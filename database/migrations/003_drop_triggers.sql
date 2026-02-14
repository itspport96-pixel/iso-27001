-- Script para deshabilitar (eliminar) triggers de auditoría
-- Los triggers se pueden restaurar ejecutando: database/migrations/001_create_triggers.sql

DROP TRIGGER IF EXISTS audit_soa_insert;
DROP TRIGGER IF EXISTS audit_soa_update;
DROP TRIGGER IF EXISTS gap_soft_delete;
DROP TRIGGER IF EXISTS update_gap_avance;
