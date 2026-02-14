-- Relaciones entre Requerimientos Base y Controles ISO 27001:2022
-- Basado en mapeo lógico de la norma

-- REQ-001: Contexto de la Organización
-- Controles críticos: políticas, roles, contactos, activos
INSERT INTO requerimientos_controles (requerimiento_base_id, control_id) VALUES
(1, (SELECT id FROM controles WHERE codigo = 'A.5.1')),  -- Políticas de seguridad
(1, (SELECT id FROM controles WHERE codigo = 'A.5.2')),  -- Roles y responsabilidades
(1, (SELECT id FROM controles WHERE codigo = 'A.5.5')),  -- Contacto con autoridades
(1, (SELECT id FROM controles WHERE codigo = 'A.5.6')),  -- Contacto con grupos de interés
(1, (SELECT id FROM controles WHERE codigo = 'A.5.9'));  -- Inventario de activos

-- REQ-002: Liderazgo
-- Controles críticos: responsabilidades de dirección, políticas
INSERT INTO requerimientos_controles (requerimiento_base_id, control_id) VALUES
(2, (SELECT id FROM controles WHERE codigo = 'A.5.1')),  -- Políticas de seguridad
(2, (SELECT id FROM controles WHERE codigo = 'A.5.2')),  -- Roles y responsabilidades
(2, (SELECT id FROM controles WHERE codigo = 'A.5.3')),  -- Segregación de funciones
(2, (SELECT id FROM controles WHERE codigo = 'A.5.4'));  -- Responsabilidades de la dirección

-- REQ-003: Planificación
-- Controles críticos: riesgos, planificación, inteligencia de amenazas
INSERT INTO requerimientos_controles (requerimiento_base_id, control_id) VALUES
(3, (SELECT id FROM controles WHERE codigo = 'A.5.7')),  -- Inteligencia de amenazas
(3, (SELECT id FROM controles WHERE codigo = 'A.5.8')),  -- Seguridad en gestión de proyectos
(3, (SELECT id FROM controles WHERE codigo = 'A.5.24')), -- Planificación gestión de incidentes
(3, (SELECT id FROM controles WHERE codigo = 'A.5.29')), -- Seguridad durante interrupciones
(3, (SELECT id FROM controles WHERE codigo = 'A.5.30')); -- Preparación TIC continuidad

-- REQ-004: Soporte
-- Controles críticos: recursos, competencia, comunicación, documentación
INSERT INTO requerimientos_controles (requerimiento_base_id, control_id) VALUES
(4, (SELECT id FROM controles WHERE codigo = 'A.5.37')), -- Procedimientos documentados
(4, (SELECT id FROM controles WHERE codigo = 'A.6.3')),  -- Capacitación en seguridad
(4, (SELECT id FROM controles WHERE codigo = 'A.6.2')),  -- Términos de empleo
(4, (SELECT id FROM controles WHERE codigo = 'A.8.6')),  -- Gestión de capacidad
(4, (SELECT id FROM controles WHERE codigo = 'A.7.11')); -- Servicios de soporte

-- REQ-005: Operación
-- Controles críticos: controles operacionales, gestión de cambios, gestión de incidentes
INSERT INTO requerimientos_controles (requerimiento_base_id, control_id) VALUES
(5, (SELECT id FROM controles WHERE codigo = 'A.5.24')), -- Planificación gestión incidentes
(5, (SELECT id FROM controles WHERE codigo = 'A.5.25')), -- Evaluación eventos
(5, (SELECT id FROM controles WHERE codigo = 'A.5.26')), -- Respuesta a incidentes
(5, (SELECT id FROM controles WHERE codigo = 'A.5.27')), -- Aprendizaje de incidentes
(5, (SELECT id FROM controles WHERE codigo = 'A.5.28')), -- Recopilación de evidencia
(5, (SELECT id FROM controles WHERE codigo = 'A.8.32')), -- Gestión de cambios
(5, (SELECT id FROM controles WHERE codigo = 'A.8.15')), -- Registro (logging)
(5, (SELECT id FROM controles WHERE codigo = 'A.8.16')); -- Monitoreo

-- REQ-006: Evaluación del Desempeño
-- Controles críticos: monitoreo, auditoría, revisión
INSERT INTO requerimientos_controles (requerimiento_base_id, control_id) VALUES
(6, (SELECT id FROM controles WHERE codigo = 'A.5.35')), -- Revisión independiente
(6, (SELECT id FROM controles WHERE codigo = 'A.5.36')), -- Cumplimiento políticas
(6, (SELECT id FROM controles WHERE codigo = 'A.8.16')), -- Actividades de monitoreo
(6, (SELECT id FROM controles WHERE codigo = 'A.8.15')), -- Registro
(6, (SELECT id FROM controles WHERE codigo = 'A.8.34')); -- Protección en auditorías

-- REQ-007: Mejora
-- Controles críticos: no conformidades, acciones correctivas, mejora continua
INSERT INTO requerimientos_controles (requerimiento_base_id, control_id) VALUES
(7, (SELECT id FROM controles WHERE codigo = 'A.5.27')), -- Aprendizaje de incidentes
(7, (SELECT id FROM controles WHERE codigo = 'A.5.35')), -- Revisión independiente
(7, (SELECT id FROM controles WHERE codigo = 'A.5.36')), -- Cumplimiento políticas
(7, (SELECT id FROM controles WHERE codigo = 'A.8.8'));  -- Gestión vulnerabilidades
