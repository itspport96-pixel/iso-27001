-- ==========================================================
-- TRIGGER: ACTUALIZA % AVANCE DEL GAP CADA VEZ QUE CAMBIA UNA ACCIÓN
-- ==========================================================

USE iso_platform;

DELIMITER //

CREATE TRIGGER trg_acciones_after_update
AFTER UPDATE ON acciones
FOR EACH ROW
BEGIN
    -- Solo si el estado cambió hacia/desde "completada"
    IF (OLD.estado_accion != NEW.estado_accion) THEN
        -- Recalcular avance
        UPDATE gap_items g
        JOIN (
            SELECT gap_id,
                   ROUND(
                       SUM(CASE WHEN estado_accion = 'completada' THEN 1 ELSE 0 END) * 100 / NULLIF(COUNT(*),0),
                       0
                   ) AS nuevo_avance
            FROM   acciones
            WHERE  estado_accion != 'eliminada'
              AND  gap_id = NEW.gap_id
        ) x ON x.gap_id = g.id
        SET g.avance = x.nuevo_avance,
            g.estado_gap = CASE
                                WHEN x.nuevo_avance = 100 THEN 'cerrado'
                                ELSE 'activo'
                           END,
            g.fecha_real_cierre = CASE
                                     WHEN x.nuevo_avance = 100 THEN NOW()
                                     ELSE NULL
                                  END;
    END IF;
END//

DELIMITER ;
