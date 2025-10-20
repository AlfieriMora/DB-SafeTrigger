<?php
/**
 * Archivo de desinstalación - Limpieza completa del plugin
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Incluir clases necesarias
require_once plugin_dir_path(__FILE__) . 'inc/class-dbst-installer.php';

/**
 * Limpieza completa al desinstalar el plugin
 */
function dbst_uninstall_cleanup() {
    global $wpdb;
    
    // 1. Eliminar todos los triggers
    $installer = new DBST_Installer();
    $installer->remove_all_triggers();
    
    // 2. Eliminar tabla log_auditoria (SIN prefijo)
    $wpdb->query("DROP TABLE IF EXISTS log_auditoria");
    
    // 3. Eliminar opciones del plugin
    delete_option('dbst_json_support');
    delete_option('dbst_last_sql_error');
    delete_option('db_safetrigger_version');
    delete_option('dbst_daily_report_enabled');
    delete_option('dbst_admin_email');
    
    // Opciones de Mailjet
    delete_option('dbst_mailjet_api_key');
    delete_option('dbst_mailjet_secret_key');
    delete_option('dbst_mailjet_from_email');
    delete_option('dbst_mailjet_from_name');
    delete_option('dbst_mailjet_sandbox_mode');
    delete_option('dbst_last_mailjet_error');
    delete_option('dbst_last_message_id');
    delete_option('dbst_last_message_uuid');
    delete_option('dbst_last_report_sent');
    delete_option('dbst_last_report_success');
    delete_option('dbst_last_email_method');
    
    // 4. Eliminar tareas cron programadas
    wp_clear_scheduled_hook('dbst_daily_audit_report');
    
    // 5. Limpiar cache
    wp_cache_flush();
}

// Ejecutar limpieza
dbst_uninstall_cleanup();