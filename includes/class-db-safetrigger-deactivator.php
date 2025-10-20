<?php
/**
 * Clase para manejar la desactivación del plugin
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DB_SafeTrigger_Deactivator {
    
    /**
     * Función ejecutada al desactivar el plugin
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        global $wpdb;
        
        // Eliminar triggers creados por el plugin
        self::remove_all_triggers();
        
        // Limpiar tareas cron programadas
        wp_clear_scheduled_hook('dbst_daily_audit_report');
        
        // Limpiar opciones temporales
        self::cleanup_temp_options();
        
        // Nota: No eliminamos las tablas de logs para preservar el historial
        // El administrador puede eliminarlas manualmente si lo desea
    }
    
    /**
     * Eliminar todos los triggers creados por el plugin
     */
    private static function remove_all_triggers() {
        global $wpdb;
        
        $triggers_table = $wpdb->prefix . 'db_safetrigger_triggers';
        
        // Obtener todos los triggers activos
        $triggers = $wpdb->get_results(
            "SELECT trigger_name FROM $triggers_table WHERE is_active = 1"
        );
        
        foreach ($triggers as $trigger) {
            // Eliminar trigger de la base de datos
            $wpdb->query($wpdb->prepare(
                "DROP TRIGGER IF EXISTS %s",
                $trigger->trigger_name
            ));
        }
        
        // Marcar triggers como inactivos
        $wpdb->update(
            $triggers_table,
            array('is_active' => 0),
            array('is_active' => 1)
        );
    }
    
    /**
     * Limpiar opciones temporales
     */
    private static function cleanup_temp_options() {
        global $wpdb;
        
        // Eliminar opciones de triggers pendientes
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE 'db_safetrigger_pending_triggers_%'"
        );
        
        // Limpiar cache
        wp_cache_flush();
    }
}