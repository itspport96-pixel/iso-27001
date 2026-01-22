-- Triggers para auditoría automática

DELIMITER $$

-- Trigger: Auditar INSERT en soa_entries
CREATE TRIGGER audit_soa_insert AFTER INSERT ON soa_entries
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (empresa_id, usuario_id, tabla, accion, registro_id, datos_nuevos, ip)
    VALUES (NEW.empresa_id, NULL, 'soa_entries', 'INSERT', NEW.id, 
            JSON_OBJECT('control_id', NEW.control_id, 'aplicable', NEW.aplicable, 'estado', NEW.estado),
            '0.0.0.0');
END$$

-- Trigger: Auditar UPDATE en soa_entries
CREATE TRIGGER audit_soa_update AFTER UPDATE ON soa_entries
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (empresa_id, usuario_id, tabla, accion, registro_id, datos_previos, datos_nuevos, ip)
    VALUES (NEW.empresa_id, NULL, 'soa_entries', 'UPDATE', NEW.id,
            JSON_OBJECT('aplicable', OLD.aplicable, 'estado', OLD.estado),
            JSON_OBJECT('aplicable', NEW.aplicable, 'estado', NEW.estado),
            '0.0.0.0');
END$$

-- Trigger: Soft delete en acciones cuando se elimina gap
CREATE TRIGGER gap_soft_delete AFTER UPDATE ON gap_items
FOR EACH ROW
BEGIN
    IF NEW.estado_gap = 'eliminado' AND OLD.estado_gap = 'activo' THEN
        UPDATE acciones SET estado_accion = 'eliminado' WHERE gap_id = NEW.id;
    END IF;
END$$

-- Trigger: Actualizar avance del gap cuando cambia estado de acción
CREATE TRIGGER update_gap_avance AFTER UPDATE ON acciones
FOR EACH ROW
BEGIN
    DECLARE total_acciones INT;
    DECLARE completadas INT;
    DECLARE nuevo_avance DECIMAL(5,2);
    
    IF NEW.estado_accion = 'activo' THEN
        SELECT COUNT(*) INTO total_acciones 
        FROM acciones 
        WHERE gap_id = NEW.gap_id AND estado_accion = 'activo';
        
        SELECT COUNT(*) INTO completadas 
        FROM acciones 
        WHERE gap_id = NEW.gap_id 
        AND estado_accion = 'activo' 
        AND estado = 'completada';
        
        IF total_acciones > 0 THEN
            SET nuevo_avance = (completadas / total_acciones) * 100;
        ELSE
            SET nuevo_avance = 0;
        END IF;
        
        UPDATE gap_items SET avance = nuevo_avance WHERE id = NEW.gap_id;
        
        IF nuevo_avance = 100 THEN
            UPDATE gap_items SET fecha_real_cierre = CURDATE() WHERE id = NEW.gap_id;
        END IF;
    END IF;
END$$

DELIMITER ;
