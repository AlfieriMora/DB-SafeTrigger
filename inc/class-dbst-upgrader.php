<?php
/**
 * Clase DBST_Upgrader - Manejo de actualizaciones de base de datos
 *
 * @package DB_SafeTrigger
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DBST_Upgrader {
    
    /**
     * Ejecutar actualizaciones necesarias
     */
    public static function upgrade() {
        $current_version = get_option('db_safetrigger_version', '1.0.0');
        
        // Actualización a versión 1.1.0 - Agregar campo wp_user_id
        if (version_compare($current_version, '1.1.0', '<')) {
            self::upgrade_to_1_1_0();
        }
        
        // Actualizar versión almacenada
        update_option('db_safetrigger_version', DB_SAFETRIGGER_VERSION);
    }
    
    /**
     * Actualización a versión 1.1.0
     * - Agregar campo wp_user_id para mejorar trazabilidad
     * - Agregar índices optimizados
     */
    private static function upgrade_to_1_1_0() {
        global $wpdb;
        
        $table_name = 'log_auditoria';
        
        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            return false;
        }
        
        // Verificar si el campo wp_user_id ya existe
        $column_exists = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM information_schema.COLUMNS 
            WHERE TABLE_NAME = '$table_name' 
            AND COLUMN_NAME = 'wp_user_id'
        ");
        
        if (!$column_exists) {
            // Agregar campo wp_user_id
            $wpdb->query("
                ALTER TABLE $table_name 
                ADD COLUMN wp_user_id BIGINT UNSIGNED NULL 
                AFTER db_user
            ");
            
            // Agregar referencia a wp_users (sin FK por compatibilidad)
            $wpdb->query("
                ALTER TABLE $table_name 
                ADD INDEX idx_wp_user_time (wp_user_id, event_time)
            ");
            
            if ($wpdb->last_error) {
                update_option('dbst_last_sql_error', 'Upgrade 1.1.0: ' . $wpdb->last_error);
                return false;
            }
        }
        
        // Actualizar configuración
        update_option('dbst_upgrade_1_1_0_completed', 1);
        update_option('dbst_triggers_need_update', 1);
        
        return true;
    }
    
    /**
     * Verificar si necesita actualizaciones
     */
    public static function needs_upgrade() {
        $current_version = get_option('db_safetrigger_version', '1.0.0');
        return version_compare($current_version, DB_SAFETRIGGER_VERSION, '<');
    }
    
    /**
     * Obtener información de actualización disponible
     */
    public static function get_upgrade_info() {
        $current_version = get_option('db_safetrigger_version', '1.0.0');
        
        $upgrades = array();
        
        if (version_compare($current_version, '1.1.0', '<')) {
            $upgrades[] = array(
                'version' => '1.1.0',
                'title' => 'Mejora de Trazabilidad de Usuarios',
                'description' => 'Agrega seguimiento del usuario de WordPress que realiza cambios',
                'changes' => array(
                    'Nuevo campo wp_user_id en tabla log_auditoria',
                    'Triggers actualizados para capturar usuario WordPress',
                    'Interfaz mejorada con nombres de usuario',
                    'Filtros por usuario en logs'
                )
            );
        }
        
        return $upgrades;
    }
}