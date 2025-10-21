<?php
/**
 * Clase DBST_Installer - Manejo de triggers e instalación/desinstalación
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DBST_Installer {
    
    /**
     * Tablas MVP a monitorear
     */
    private $monitor_tables = array('posts', 'users', 'comments');
    
    /**
     * Crear triggers para una tabla específica
     *
     * @param string $table_name Nombre de tabla sin prefijo
     * @return bool
     */
    public function create_table_triggers($table_name) {
        global $wpdb;
        
        $prefixed_table = $wpdb->prefix . $table_name;
        
        // Verificar que la tabla existe
        if (!$this->table_exists($prefixed_table)) {
            return false;
        }
        
        $success = true;
        
        // Crear trigger BEFORE UPDATE
        $success &= $this->create_trigger($table_name, 'UPDATE');
        
        // Crear trigger BEFORE DELETE  
        $success &= $this->create_trigger($table_name, 'DELETE');
        
        return $success;
    }
    
    /**
     * Crear un trigger específico
     *
     * @param string $table_name
     * @param string $action UPDATE|DELETE
     * @return bool
     */
    private function create_trigger($table_name, $action) {
        global $wpdb;
        
        $prefixed_table = $wpdb->prefix . $table_name;
        $trigger_name = $this->get_trigger_name($table_name, $action);
        
        // Primero eliminar trigger si existe (idempotencia)
        $wpdb->query("DROP TRIGGER IF EXISTS `$trigger_name`");
        
        // Obtener campos a monitorear según la tabla
        $fields = $this->get_table_fields($table_name);
        $old_data_json = $this->build_old_data_json($fields);
        
        // Crear el trigger con soporte para usuario WordPress
        $trigger_sql = "
        CREATE TRIGGER `$trigger_name`
        BEFORE $action ON `$prefixed_table`
        FOR EACH ROW
        BEGIN
            INSERT INTO log_auditoria (
                event_time,
                db_user,
                wp_user_id,
                table_name,
                action,
                pk_value,
                old_data,
                client_host
            ) VALUES (
                NOW(),
                CURRENT_USER(),
                @wp_current_user_id,
                '$prefixed_table',
                '$action',
                OLD.{$this->get_primary_key($table_name)},
                $old_data_json,
                SUBSTRING_INDEX(USER(),'@',-1)
            );
        END";
        
        $result = $wpdb->query($trigger_sql);
        
        if ($wpdb->last_error) {
            update_option('dbst_last_sql_error', $wpdb->last_error);
            return false;
        }
        
        return $result !== false;
    }
    
    /**
     * Generar nombre de trigger consistente
     */
    private function get_trigger_name($table_name, $action) {
        $suffix = ($action === 'UPDATE') ? 'bu' : 'bd';
        return "trg_{$table_name}_{$suffix}";
    }
    
    /**
     * Obtener campos a monitorear por tabla según especificación
     */
    private function get_table_fields($table_name) {
        $fields_map = array(
            'posts' => array('ID', 'post_author', 'post_date', 'post_title', 'post_status', 'post_name', 'post_modified', 'post_type'),
            'users' => array('ID', 'user_login', 'user_email', 'user_registered', 'display_name'),
            'comments' => array('comment_ID', 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_date', 'comment_content', 'comment_approved')
        );
        
        return isset($fields_map[$table_name]) ? $fields_map[$table_name] : array();
    }
    
    /**
     * Obtener clave primaria por tabla
     */
    private function get_primary_key($table_name) {
        $pk_map = array(
            'posts' => 'ID',
            'users' => 'ID', 
            'comments' => 'comment_ID'
        );
        
        return isset($pk_map[$table_name]) ? $pk_map[$table_name] : 'ID';
    }
    
    /**
     * Construir JSON de old_data según soporte de BD
     */
    private function build_old_data_json($fields) {
        $supports_json = get_option('dbst_json_support', false);
        
        if ($supports_json) {
            // Usar JSON_OBJECT para MySQL 5.7+
            $field_pairs = array();
            foreach ($fields as $field) {
                $field_pairs[] = "'$field', OLD.$field";
            }
            return 'JSON_OBJECT(' . implode(', ', $field_pairs) . ')';
        } else {
            // Fallback a string JSON manual
            $field_pairs = array();
            foreach ($fields as $field) {
                $field_pairs[] = "\"$field\":\", IFNULL(QUOTE(OLD.$field), 'null'), \"";
            }
            return 'CONCAT("{", ' . implode(', ",", ', $field_pairs) . ', "}")';
        }
    }
    
    /**
     * Verificar si una tabla existe
     */
    private function table_exists($table_name) {
        global $wpdb;
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        ));
        
        return $result === $table_name;
    }
    
    /**
     * Eliminar todos los triggers del plugin
     */
    public function remove_all_triggers() {
        global $wpdb;
        
        foreach ($this->monitor_tables as $table) {
            $this->remove_table_triggers($table);
        }
    }
    
    /**
     * Eliminar triggers de una tabla específica
     */
    private function remove_table_triggers($table_name) {
        global $wpdb;
        
        $triggers = array(
            $this->get_trigger_name($table_name, 'UPDATE'),
            $this->get_trigger_name($table_name, 'DELETE')
        );
        
        foreach ($triggers as $trigger_name) {
            $wpdb->query("DROP TRIGGER IF EXISTS `$trigger_name`");
        }
    }
}