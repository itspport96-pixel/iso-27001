-- Seed: 7 Requerimientos Obligatorios ISO 27001

INSERT INTO requerimientos_base (numero, identificador, titulo, descripcion) VALUES
(1, 'REQ-001', 'Contexto de la Organización', 'Determinar cuestiones internas y externas, partes interesadas y alcance del SGSI'),
(2, 'REQ-002', 'Liderazgo y Compromiso', 'Política de seguridad, roles y responsabilidades definidos por la dirección'),
(3, 'REQ-003', 'Planificación', 'Evaluación de riesgos, tratamiento de riesgos y objetivos de seguridad establecidos'),
(4, 'REQ-004', 'Soporte', 'Recursos, competencias, conciencia, comunicación y documentación implementados'),
(5, 'REQ-005', 'Operación', 'Evaluación y tratamiento de riesgos operacionales implementados'),
(6, 'REQ-006', 'Evaluación del Desempeño', 'Monitoreo, medición, análisis, evaluación y auditoría interna realizados'),
(7, 'REQ-007', 'Mejora Continua', 'No conformidades, acciones correctivas y mejora continua del SGSI');

-- Relacionar requerimientos con controles críticos
INSERT INTO requerimientos_controles (requerimiento_base_id, control_id) VALUES
-- REQ-001: Contexto (A.5.1, A.5.7)
(1, 1), (1, 4),
-- REQ-002: Liderazgo (A.5.1, A.5.2, A.5.3)
(2, 1), (2, 2), (2, 3),
-- REQ-003: Planificación (A.5.7, A.8.8)
(3, 4), (3, 21),
-- REQ-004: Soporte (A.6.2, A.6.3, A.7.1)
(4, 7), (4, 8), (4, 11),
-- REQ-005: Operación (A.8.1, A.8.2, A.8.3, A.8.5)
(5, 16), (5, 17), (5, 18), (5, 20),
-- REQ-006: Evaluación (A.8.16)
(6, 25),
-- REQ-007: Mejora (A.5.2, A.6.4)
(7, 2), (7, 9);
