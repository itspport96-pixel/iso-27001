-- ==========================================================
-- ISO 27001:2022 – 7 REQUERIMIENTOS DOCUMENTALES OBLIGATORIOS
-- ==========================================================

USE iso_platform;

INSERT INTO requerimientos_base (numero, identificador, descripcion) VALUES
(1,'MANUAL_POLITICAS','Manual de políticas de seguridad de la información: documento maestro que establece la dirección y principios de seguridad de la organización.'),
(2,'INVENTARIO_ACTIVOS','Inventario de activos de información: registro completo y actualizado de todos los activos de información clasificados.'),
(3,'PLAN_CAPACITACIONES','Plan anual de capacitaciones en seguridad de la información: programa formativo para todo el personal interno y externo.'),
(4,'ESTRATEGIA_CONCIENCIACION','Estrategia de concienciación en seguridad de la información: campañas y métodos para mantener la conciencia en seguridad.'),
(5,'EVIDENCIA_CUMPLIMIENTO_PLAN','Evidencia de cumplimiento del plan de capacitaciones y estrategia de concienciación: registros de asistencia, evaluaciones y resultados.'),
(6,'MANUAL_GESTION_INCIDENTES','Manual de gestión de incidentes de seguridad: procedimientos para identificar, clasificar, responder y cerrar incidentes.'),
(7,'EVIDENCIA_MONITOREO_CONTINUO','Evidencia de monitoreo continuo: registros periódicos que demuestran la supervisión activa de los controles implementados.');

-- Vinculación requerimiento ↔ controles (solo ejemplos ilustrativos)
INSERT INTO requerimientos_controles (requerimiento_base_id, control_id)
SELECT 1, id FROM controles WHERE codigo IN ('5.1','5.2','5.3'); -- Manual políticas

INSERT INTO requerimientos_controles (requerimiento_base_id, control_id)
SELECT 2, id FROM controles WHERE codigo IN ('5.7','5.8','5.9'); -- Inventario activos

INSERT INTO requerimientos_controles (requerimiento_base_id, control_id)
SELECT 3, id FROM controles WHERE codigo IN ('6.4'); -- Plan capacitaciones

INSERT INTO requerimientos_controles (requerimiento_base_id, control_id)
SELECT 4, id FROM controles WHERE codigo IN ('6.4'); -- Estrategia concienciación

INSERT INTO requerimientos_controles (requerimiento_base_id, control_id)
SELECT 5, id FROM controles WHERE codigo IN ('6.4'); -- Evidencia cumplimiento

INSERT INTO requerimientos_controles (requerimiento_base_id, control_id)
SELECT 6, id FROM controles WHERE codigo IN ('5.24','5.25','5.26'); -- Gestión incidentes

INSERT INTO requerimientos_controles (requerimiento_base_id, control_id)
SELECT 7, id FROM controles WHERE codigo IN ('5.20'); -- Monitoreo continuo
