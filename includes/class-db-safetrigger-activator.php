<?php
/**
 * Clase para manejar la activación del plugin
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DB_SafeTrigger_Activator {
    
    /**
     * Función ejecutada al activar el plugin
     *
     * @since 1.0.0
     */
    public static function activate() {
        global $wpdb;
        
        // Crear tabla para almacenar logs de auditoría
        self::create_audit_tables();
        
        // Crear triggers iniciales para tablas principales de WordPress
        self::create_initial_triggers();
        
        // Establecer versión en la base de datos
        add_option('db_safetrigger_version', DB_SAFETRIGGER_VERSION);
        
        // Configuraciones por defecto
        add_option('db_safetrigger_enabled', 1);
        add_option('db_safetrigger_monitor_tables', array(
            'posts',
            'users',
            'comments'
        ));
        
        // Configuraciones de email y cron
        add_option('dbst_daily_report_enabled', 1);
        add_option('dbst_admin_email', get_option('admin_email'));
        
        // Configuraciones de Mailjet (vacías por defecto)
        add_option('dbst_mailjet_api_key', '');
        add_option('dbst_mailjet_secret_key', '');
        add_option('dbst_mailjet_from_email', '');
        add_option('dbst_mailjet_from_name', get_bloginfo('name'));
        add_option('dbst_mailjet_sandbox_mode', false);
    }
    
    /**
     * Crear tabla de auditoría según especificación
     */
    private static function create_audit_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Detectar soporte JSON
        $supports_json = self::check_json_support();
        $old_data_type = $supports_json ? 'JSON' : 'LONGTEXT';
        
        // Tabla log_auditoria (SIN prefijo según especificación)
        $table_name = 'log_auditoria';
        
        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            db_user VARCHAR(128),
            table_name VARCHAR(128) NOT NULL,
            action ENUM('UPDATE','DELETE') NOT NULL,
            pk_value VARCHAR(64) NOT NULL,
            old_data $old_data_type,
            client_host VARCHAR(255),
            PRIMARY KEY (id),
            KEY idx_table_time (table_name, event_time),
            KEY idx_action_time (action, event_time)
        ) ENGINE=InnoDB $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Guardar configuración de soporte JSON
        update_option('dbst_json_support', $supports_json);
        
        // Registrar último error SQL si lo hay
        if ($wpdb->last_error) {
            update_option('dbst_last_sql_error', $wpdb->last_error);
        }
    }
    
    /**
     * Verificar soporte para tipo JSON en MySQL/MariaDB
     */
    private static function check_json_support() {
        global $wpdb;
        
        // Probar crear tabla temporal con JSON
        $test_result = $wpdb->query("
            CREATE TEMPORARY TABLE dbst_json_test (
                test_col JSON
            )
        ");
        
        if ($test_result !== false) {
            $wpdb->query("DROP TEMPORARY TABLE IF EXISTS dbst_json_test");
            return true;
        }
        
        return false;
    }
    
    /**
     * Crear triggers iniciales para tablas MVP
     */
    private static function create_initial_triggers() {
        // Tablas MVP según especificación
        $monitor_tables = array('posts', 'users', 'comments');
        
        // Verificar privilegios TRIGGER
        if (!self::check_trigger_privileges()) {
            update_option('dbst_last_sql_error', 'Usuario MySQL sin privilegio TRIGGER');
            return;
        }
        
        // Marcar para crear triggers después
        update_option('dbst_create_triggers_needed', 1);
        update_option('dbst_monitor_tables', $monitor_tables);
    }
    
    /**
     * Verificar privilegios TRIGGER del usuario MySQL
     */
    private static function check_trigger_privileges() {
        global $wpdb;
        
        $result = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM information_schema.USER_PRIVILEGES 
            WHERE PRIVILEGE_TYPE = 'TRIGGER' 
            AND GRANTEE LIKE CONCAT(\"'\", USER(), \"'%\")
        ");
        
        return $result > 0;
    }
}