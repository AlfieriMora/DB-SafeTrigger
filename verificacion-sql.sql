-- Script de verificación SQL para DB-SafeTrigger
-- Ejecutar estas consultas para verificar el estado del sistema

-- 1. Verificar que la tabla log_auditoria existe
SELECT 
    'Tabla log_auditoria' as verificacion,
    CASE 
        WHEN COUNT(*) > 0 THEN 'EXISTE ✓' 
        ELSE 'NO EXISTE ✗' 
    END as estado
FROM information_schema.tables 
WHERE table_name = 'log_auditoria';

-- 2. Verificar estructura de la tabla log_auditoria
DESCRIBE log_auditoria;

-- 3. Verificar triggers existentes para tablas monitoreadas
SHOW TRIGGERS LIKE '%posts%';
SHOW TRIGGERS LIKE '%users%';  
SHOW TRIGGERS LIKE '%comments%';

-- 4. Contar registros de auditoría del día actual
SELECT 
    'Eventos hoy' as verificacion,
    COUNT(*) as total_eventos,
    DATE(NOW()) as fecha
FROM log_auditoria 
WHERE DATE(event_time) = CURDATE();

-- 5. Resumen por tabla y acción (últimos 7 días)
SELECT 
    table_name,
    action,
    COUNT(*) as total_eventos,
    MIN(event_time) as primer_evento,
    MAX(event_time) as ultimo_evento
FROM log_auditoria 
WHERE event_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY table_name, action
ORDER BY total_eventos DESC;

-- 6. Verificar validez de JSON en old_data (solo si usa JSON)
SELECT 
    'Validez JSON' as verificacion,
    COUNT(*) as total_registros,
    SUM(CASE WHEN JSON_VALID(old_data) THEN 1 ELSE 0 END) as json_validos,
    SUM(CASE WHEN JSON_VALID(old_data) THEN 0 ELSE 1 END) as json_invalidos
FROM log_auditoria 
WHERE old_data IS NOT NULL
LIMIT 1000;

-- 7. Verificar privilegios TRIGGER del usuario actual
SELECT 
    'Privilegio TRIGGER' as verificacion,
    CASE 
        WHEN COUNT(*) > 0 THEN 'DISPONIBLE ✓' 
        ELSE 'NO DISPONIBLE ✗' 
    END as estado
FROM information_schema.USER_PRIVILEGES 
WHERE PRIVILEGE_TYPE = 'TRIGGER' 
AND GRANTEE LIKE CONCAT("'", USER(), "'%");

-- 8. Eventos recientes (últimas 10 entradas)
SELECT 
    id,
    event_time,
    table_name,
    action,
    pk_value,
    db_user,
    client_host
FROM log_auditoria 
ORDER BY id DESC 
LIMIT 10;

-- 9. Estadísticas de volumen por día (últimos 30 días)
SELECT 
    DATE(event_time) as fecha,
    COUNT(*) as total_eventos,
    COUNT(CASE WHEN action = 'UPDATE' THEN 1 END) as updates,
    COUNT(CASE WHEN action = 'DELETE' THEN 1 END) as deletes
FROM log_auditoria 
WHERE event_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(event_time)
ORDER BY fecha DESC;

-- 10. Verificar tamaño de la tabla log_auditoria
SELECT 
    'Tamaño tabla' as verificacion,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) as 'tamaño_mb',
    table_rows as 'total_filas'
FROM information_schema.TABLES 
WHERE table_name = 'log_auditoria';

-- PRUEBAS DE TRIGGERS (ejecutar solo en entorno de pruebas)
-- Descomenta las siguientes líneas para probar los triggers:

/*
-- Prueba trigger UPDATE en posts
UPDATE wp_posts SET post_title = CONCAT(post_title, ' [PRUEBA]') WHERE ID = 1 LIMIT 1;

-- Verificar que se insertó registro en log_auditoria
SELECT * FROM log_auditoria WHERE table_name LIKE '%posts%' ORDER BY id DESC LIMIT 1;

-- Restaurar post original
UPDATE wp_posts SET post_title = REPLACE(post_title, ' [PRUEBA]', '') WHERE ID = 1 LIMIT 1;
*/