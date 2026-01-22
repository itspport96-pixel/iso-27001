-- Seed: Controles ISO 27001:2022 (Principales)

INSERT INTO controles (dominio_id, codigo, nombre, descripcion, objetivo) VALUES
-- Dominio A.5: Organizacionales
(1, 'A.5.1', 'Políticas de seguridad de la información', 'Políticas para la seguridad de la información definidas, aprobadas, publicadas y comunicadas', 'Proporcionar dirección y apoyo de la gestión para la seguridad de la información'),
(1, 'A.5.2', 'Roles y responsabilidades', 'Roles y responsabilidades de seguridad de la información definidos y asignados', 'Establecer y mantener roles y responsabilidades apropiadas'),
(1, 'A.5.3', 'Segregación de funciones', 'Funciones y áreas de responsabilidad en conflicto segregadas', 'Reducir oportunidades de modificación o uso indebido'),
(1, 'A.5.7', 'Inteligencia de amenazas', 'Información sobre amenazas recopilada y analizada', 'Asegurar conciencia de las amenazas de seguridad'),
(1, 'A.5.10', 'Uso aceptable de la información', 'Reglas para el uso aceptable de información y activos definidas', 'Proteger la información y activos asociados'),

-- Dominio A.6: Personas
(2, 'A.6.1', 'Selección', 'Verificación de antecedentes en candidatos', 'Asegurar que las personas son confiables y adecuadas'),
(2, 'A.6.2', 'Términos y condiciones de empleo', 'Términos contractuales que incluyen responsabilidades de seguridad', 'Asegurar comprensión de responsabilidades'),
(2, 'A.6.3', 'Concienciación, educación y capacitación', 'Personal capacitado en políticas y procedimientos', 'Asegurar conciencia sobre amenazas y responsabilidades'),
(2, 'A.6.4', 'Proceso disciplinario', 'Proceso formal para violaciones de seguridad', 'Disuadir violaciones de políticas'),
(2, 'A.6.5', 'Responsabilidades después de la terminación', 'Responsabilidades que permanecen después del empleo', 'Proteger los intereses de la organización'),

-- Dominio A.7: Físicos
(3, 'A.7.1', 'Perímetros de seguridad física', 'Perímetros definidos y usados para proteger áreas', 'Prevenir acceso físico no autorizado'),
(3, 'A.7.2', 'Controles de entrada física', 'Áreas seguras protegidas con controles de entrada', 'Asegurar que solo personal autorizado acceda'),
(3, 'A.7.3', 'Seguridad de oficinas y recursos', 'Seguridad física diseñada para oficinas e instalaciones', 'Prevenir daño y acceso no autorizado'),
(3, 'A.7.4', 'Monitoreo de seguridad física', 'Instalaciones monitoreadas contra acceso no autorizado', 'Detectar y disuadir acceso no autorizado'),
(3, 'A.7.7', 'Escritorio y pantalla limpios', 'Reglas de escritorio limpio y pantalla limpia', 'Reducir riesgos de acceso no autorizado'),

-- Dominio A.8: Tecnológicos
(4, 'A.8.1', 'Dispositivos de punto final de usuario', 'Información en dispositivos protegida', 'Proteger información en dispositivos de usuario'),
(4, 'A.8.2', 'Derechos de acceso privilegiados', 'Asignación de derechos privilegiados restringida', 'Prevenir acceso no autorizado a sistemas'),
(4, 'A.8.3', 'Restricción de acceso a la información', 'Acceso a información restringido según política', 'Asegurar acceso autorizado solamente'),
(4, 'A.8.4', 'Acceso al código fuente', 'Acceso de lectura y escritura a código fuente controlado', 'Prevenir introducción de funcionalidad no autorizada'),
(4, 'A.8.5', 'Autenticación segura', 'Tecnologías y procedimientos de autenticación implementados', 'Asegurar identidad de usuarios y sistemas'),
(4, 'A.8.8', 'Gestión de vulnerabilidades técnicas', 'Información sobre vulnerabilidades obtenida y gestionada', 'Prevenir explotación de vulnerabilidades'),
(4, 'A.8.9', 'Gestión de configuración', 'Configuraciones de seguridad establecidas y mantenidas', 'Asegurar consistencia en configuraciones de seguridad'),
(4, 'A.8.10', 'Eliminación de información', 'Información eliminada cuando ya no es requerida', 'Prevenir divulgación innecesaria de información'),
(4, 'A.8.11', 'Enmascaramiento de datos', 'Enmascaramiento usado según política', 'Limitar exposición de datos sensibles'),
(4, 'A.8.16', 'Actividades de monitoreo', 'Redes, sistemas y aplicaciones monitoreadas', 'Detectar actividades anormales'),
(4, 'A.8.23', 'Filtrado web', 'Acceso a sitios web externos gestionado', 'Prevenir acceso a contenido malicioso'),
(4, 'A.8.24', 'Uso de criptografía', 'Reglas para uso de criptografía definidas', 'Asegurar uso apropiado de criptografía'),
(4, 'A.8.28', 'Codificación segura', 'Principios de codificación segura aplicados', 'Prevenir vulnerabilidades en software');
