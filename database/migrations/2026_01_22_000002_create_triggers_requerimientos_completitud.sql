-- ==========================================================
-- TRIGGER: MARCA REQUERIMIENTO COMO "COMPLETADO" SI TODOS SUS CONTROLES
--          ESTÁN IMPLEMENTADOS Y CON EVIDENCIAS APROBADAS
-- ==========================================================

USE iso_platform;

DELIMITER //

CREATE TRIGGER trg_evidencias_after_aprove
AFTER UPDATE ON evidencias
FOR EACH ROW
BEGIN
    IF NEW.estado_validacion = 'aprobada' AND OLD.estado_validacion != 'aprobada' THEN
        CALL sp_completar_requerimiento(NEW.empresa_id, NEW.control_id);
    END IF;
END//

CREATE TRIGGER trg_soa_after_implement
AFTER UPDATE ON soa_entries
FOR EACH ROW
BEGIN
    IF NEW.estado = 'implementado' AND OLD.estado != 'implementado' THEN
        CALL sp_completar_requerimiento(NEW.empresa_id, NEW.control_id);
    END IF;
END//

DELIMITER ;

-- Stored procedure usado por ambos triggers
DELIMITER //
CREATE PROCEDURE sp_completar_requerimiento(IN p_empresa_id INT, IN p_control_id INT)
BEGIN
    -- Para cada requerimiento asociado al control
    UPDATE empresa_requerimientos er
    JOIN (
        SELECT rb.id AS req_id
        FROM   requerimientos_base rb
        JOIN   requerimientos_controles rc ON rc.requerimiento_base_id = rb.id
        WHERE  rc.control_id = p_control_id
    ) x ON x.req_id = er.requerimiento_base_id
    SET er.estado = CASE
        WHEN (
            -- Todos los controles del requerimiento están implementados
            SELECT COUNT(*) = SUM(CASE WHEN s.estado = 'implementado' THEN 1 ELSE 0 END)
            FROM   soa_entries s
            JOIN   requerimientos_controles rc2 ON rc2.control_id = s.control_id
            WHERE  rc2.requerimiento_base_id = er.requerimiento_base_id
              AND  s.empresa_id = p_empresa_id
              AND  s.aplicable = 1
        ) AND (
            -- Todos los controles del requerimiento tienen evidencias aprobadas
            SELECT COUNT(*) = SUM(CASE WHEN e.estado_validacion = 'aprobada' THEN 1 ELSE 0 END)
            FROM   evidencias e
            JOIN   requerimientos_controles rc3 ON rc3.control_id = e.control_id
            WHERE  rc3.requerimiento_base_id = er.requerimiento_base_id
              AND  e.empresa_id = p_empresa_id
        ) THEN 'completado'
        ELSE 'en_proceso'
    END,
    er.fecha_entrega = CASE
        WHEN (
            SELECT COUNT(*) = SUM(CASE WHEN s.estado = 'implementado' THEN 1 ELSE 0 END)
            FROM   soa_entries s
            JOIN   requerimientos_controles rc2 ON rc2.control_id = s.control_id
            WHERE  rc2.requerimiento_base_id = er.requerimiento_base_id
              AND  s.empresa_id = p_empresa_id
              AND  s.aplicable = 1
        ) AND (
            SELECT COUNT(*) = SUM(CASE WHEN e.estado_validacion = 'aprobada' THEN 1 ELSE 0 END)
            FROM   evidencias e
            JOIN   requerimientos_controles rc3 ON rc3.control_id = e.control_id
            WHERE  rc3.requerimiento_base_id = er.requerimiento_base_id
              AND  e.empresa_id = p_empresa_id
        ) THEN NOW()
        ELSE NULL
    END
    WHERE er.empresa_id = p_empresa_id;
END//
DELIMITER ;
