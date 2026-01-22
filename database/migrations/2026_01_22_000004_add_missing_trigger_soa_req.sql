-- Asegura que al crear una empresa se inserten automáticamente:
--   1. 93 filas en soa_entries (estado=no_implementado, aplicable=1)
--   2. 7 filas en empresa_requerimientos (estado=pendiente)

USE iso_platform;

DELIMITER //

CREATE TRIGGER trg_after_empresa_insert
AFTER INSERT ON empresas
FOR EACH ROW
BEGIN
    -- 1. SOA entries
    INSERT INTO soa_entries (empresa_id, control_id, aplicable, estado, justificacion)
    SELECT NEW.id, c.id, 1, 'no_implementado', ''
    FROM   controles c;

    -- 2. Requerimientos base
    INSERT INTO empresa_requerimientos (empresa_id, requerimiento_base_id, estado)
    SELECT NEW.id, rb.id, 'pendiente'
    FROM   requerimientos_base rb;
END//

DELIMITER ;
