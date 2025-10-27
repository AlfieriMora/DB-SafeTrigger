<?php
/**
 * Plugin Name: DB-SafeTrigger
 * Plugin URI: https://github.com/AlfieriMora/DB-SafeTrigger
 * Description: Sistema de Auditoría y Trazabilidad de Base de Datos para WordPress con integración Mailjet
 * Version: 1.1.0
 * Author: Alfieri Mora, Kriscia Campos, Ernesto Valerio, Eddy Alfaro
 * Author URI: https://github.com/AlfieriMora
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: db-safetrigger
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cargar dominio de texto para internacionalización
 */
function dbst_load_textdomain() {
    static $loaded = false;
    
    // Evitar cargar múltiples veces
    if ($loaded !== false) return $loaded;
    
    $locale = get_locale();
    $domain = 'db-safetrigger';
    $plugin_rel_path = dirname(plugin_basename(__FILE__)) . '/languages/';
    
    // Limpiar cualquier textdomain previo
    unload_textdomain($domain);
    
    // Intentar cargar traducciones
    $loaded = false;
    
    // Método 1: Función estándar de WordPress
    $loaded = load_plugin_textdomain($domain, false, $plugin_rel_path);
    
    // Método 2: Cargar archivo específico si el anterior falló
    if (!$loaded) {
        $mofile = WP_PLUGIN_DIR . '/' . $plugin_rel_path . $domain . '-' . $locale . '.mo';
        if (file_exists($mofile)) {
            $loaded = load_textdomain($domain, $mofile);
        }
    }
    
    // Método 3: Intentar cargar en_US si el idioma actual no funciona
    if (!$loaded && $locale !== 'en_US') {
        $mofile_en = WP_PLUGIN_DIR . '/' . $plugin_rel_path . $domain . '-en_US.mo';
        if (file_exists($mofile_en)) {
            $loaded = load_textdomain($domain, $mofile_en);
        }
    }
    
    // Siempre registrar fallback como respaldo
    add_filter('gettext_with_context', 'dbst_fallback_translations', 10, 4);
    add_filter('gettext', 'dbst_fallback_translations_simple', 10, 3);
    
    return $loaded;
}

/**
 * Filtro para traducir la descripción del plugin en la lista de plugins
 */
function dbst_translate_plugin_description($plugin_data, $plugin_file) {
    $plugin_name = plugin_basename(__FILE__);
    
    if ($plugin_file === $plugin_name) {
        $locale = get_locale();
        
        // Solo traducir si está en inglés
        if ($locale === 'en_US' || strpos($locale, 'en_') === 0) {
            $plugin_data['Description'] = 'Database Audit and Traceability System for WordPress with Mailjet integration';
        }
    }
    
    return $plugin_data;
}
add_filter('all_plugins', function($plugins) {
    foreach ($plugins as $plugin_file => $plugin_data) {
        $plugins[$plugin_file] = dbst_translate_plugin_description($plugin_data, $plugin_file);
    }
    return $plugins;
});

/**
 * Traducciones de fallback cuando no hay archivos .mo
 */
function dbst_fallback_translations_simple($translation, $text, $domain) {
    if ($domain !== 'db-safetrigger') {
        return $translation;
    }
    
    // Si ya hay una traducción diferente al texto original, usarla
    if ($translation !== $text) {
        return $translation;
    }
    
    $locale = get_locale();
    
    // Traducciones SOLO para inglés (cuando WordPress está en inglés)
    if ($locale === 'en_US' || strpos($locale, 'en_') === 0) {
        $translations = array(
            'Estado del Sistema' => 'System Status',
            'Gestión de Triggers' => 'Trigger Management', 
            'Configuración Mailjet' => 'Mailjet Configuration',
            'Reportes' => 'Reports',
            'Logs de Auditoría' => 'Audit Logs',
            'Sistema de Auditoría y Trazabilidad de Base de Datos para WordPress' => 'Database Audit and Traceability System for WordPress',
            'Información general sobre el estado del sistema de auditoría.' => 'General information about the audit system status.',
            'Tabla de Auditoría' => 'Audit Table',
            'Configurada correctamente' => 'Configured correctly',
            'No encontrada' => 'Not found',
            'Triggers Activos' => 'Active Triggers',
            'Sistema funcionando' => 'System working',
            'Triggers no configurados' => 'Triggers not configured',
            'Total de Logs' => 'Total Logs',
            'Desde el inicio' => 'Since start',
            'Logs Hoy' => 'Logs Today',    
            'Gestión de Triggers de Auditoría' => 'Audit Triggers Management',
            'Administra y monitorea los triggers automáticos para la trazabilidad de datos.' => 'Manage and monitor automatic triggers for data traceability.',
            'Control de Triggers' => 'Trigger Control',
            'Crear/Actualizar Triggers' => 'Create/Update Triggers',
            'Eliminar Todos' => 'Delete All',
            'Estado de Monitoreo' => 'Monitoring Status',
            'Activo' => 'Active',
            'Inactivo' => 'Inactive',
            'No hay triggers configurados' => 'No triggers configured',
            'El sistema de auditoría no está activo. Configura los triggers para comenzar el monitoreo.' => 'The audit system is not active. Configure triggers to start monitoring.',
            'Configurar Triggers Ahora' => 'Configure Triggers Now',
            'Triggers Configurados' => 'Configured Triggers',
            'Tabla:' => 'Table:',
            'Evento:' => 'Event:',
            'Configura el sistema de envío de reportes por email usando Mailjet API.' => 'Configure the email report system using Mailjet API.',
            'Credenciales de API' => 'API Credentials',
            'API Key' => 'API Key',
            'Secret Key' => 'Secret Key',
            'Email Remitente' => 'Sender Email',
            'Nombre del Remitente' => 'Sender Name',
            'Destinatarios de Reportes' => 'Report Recipients',
            'Guardar Configuración' => 'Save Configuration',
            'Estado de la Configuración' => 'Configuration Status',
            'Configurada' => 'Configured',
            'No configurada' => 'Not configured',
            'Configurado' => 'Configured',
            'Opcional' => 'Optional',
            'Destinatarios' => 'Recipients',
            'configurados' => 'configured',
            'Sistema Listo' => 'System Ready',
            'Configuración Incompleta' => 'Incomplete Configuration',
            'Los reportes se pueden enviar' => 'Reports can be sent',
            'Complete la configuración para enviar reportes' => 'Complete configuration to send reports',
            'Información' => 'Information',
            'Tablas monitoreadas:' => 'Monitored tables:',
            'Eventos detectados:' => 'Detected events:',
            'Datos capturados:' => 'Captured data:',
            'Usuario, timestamp, cambios' => 'User, timestamp, changes',
            'Gestión de Reportes Avanzada' => 'Advanced Report Management',
            'Configura reportes automáticos personalizados y envía reportes instantáneos con los datos actuales.' => 'Configure custom automatic reports and send instant reports with current data.',
            'Estadísticas de Hoy' => 'Today\'s Statistics',
            'Total Eventos' => 'Total Events',
            'Actualizaciones' => 'Updates',
            'Eliminaciones' => 'Deletions',
            'Enviar Reporte Ahora' => 'Send Report Now',
            'Configuración de Reportes' => 'Report Configuration',
            'Activar reportes automáticos' => 'Enable automatic reports',
            'Frecuencia:' => 'Frequency:',
            'Diario' => 'Daily',
            'Semanal' => 'Weekly',
            'Mensual' => 'Monthly',
            'Hora de envío:' => 'Send time:',
            'Destinatarios (separados por comas):' => 'Recipients (comma separated):',
            'Contenido del reporte:' => 'Report content:',
            'Incluir resumen estadístico' => 'Include statistical summary',
            'Incluir detalles de eventos' => 'Include event details',
            'Estado del Sistema de Reportes' => 'Report System Status',
            'Reportes Automáticos:' => 'Automatic Reports:',
            'Activados' => 'Enabled',
            'Desactivados' => 'Disabled',
            'Próximo Envío:' => 'Next Send:',
            'No programado' => 'Not scheduled',
            'Último Envío:' => 'Last Send:',
            'Nunca' => 'Never',
            'Total Destinatarios:' => 'Total Recipients:',
            'Usuario WordPress:' => 'WordPress User:',
            'Acción:' => 'Action:',
            'Todas las acciones' => 'All actions',
            'Todas las tablas' => 'All tables',
            'Todos los usuarios' => 'All users',
            '✅ Configuración de reportes guardada correctamente.' => '✅ Report configuration saved successfully.',
            '✅ Reporte diario activado.' => '✅ Daily report activated.',
            '⏸️ Reporte diario desactivado.' => '⏸️ Daily report deactivated.',
            'Sistema de Auditoría y Trazabilidad de Base de Datos para WordPress con integración Mailjet' => 'Database Audit and Traceability System for WordPress with Mailjet integration',
            'Deactivate' => 'Deactivate',
            'Version' => 'Version',
            'By' => 'By',
            'Visit plugin site' => 'Visit plugin site',
            'Filtrar' => 'Filter',
            'Limpiar' => 'Clear',
            'Mostrando' => 'Showing',
            'de' => 'of',
            'registros' => 'records',
            'No se encontraron logs con los filtros aplicados.' => 'No logs found with the applied filters.',
            'Registro completo de cambios detectados en las tablas monitoreadas con información de usuario WordPress.' => 'Complete record of changes detected in monitored tables with WordPress user information.',
            'Posts' => 'Posts',
            'Users' => 'Users',
            'Comments' => 'Comments',
            'UPDATE y DELETE' => 'UPDATE and DELETE',
            'Información' => 'Information',
            'Configuración guardada correctamente.' => 'Configuration saved successfully.',
            'Estado de la Configuración' => 'Configuration Status',
            'Obtén tu API Key desde tu panel de Mailjet' => 'Get your API Key from your Mailjet panel',
            'Tu Secret Key privada (mantenida en secreto)' => 'Your private Secret Key (keep it secret)',
            'Email desde el cual se enviarán los reportes' => 'Email from which reports will be sent',
            'Nombre que aparecerá como remitente' => 'Name that will appear as sender',
            'Un email por línea. Estos usuarios recibirán los reportes automáticos' => 'One email per line. These users will receive automatic reports',
            'Gestión de Reportes Avanzada' => 'Advanced Report Management',
            'Configura reportes automáticos personalizados y envía reportes instantáneos con los datos actuales.' => 'Configure custom automatic reports and send instant reports with current data.',
            'Estadísticas de Hoy' => 'Today\'s Statistics',
            'Configuración de Reportes' => 'Report Configuration',
            'Activar reportes automáticos' => 'Enable automatic reports',
            'Hora de envío:' => 'Send time:',
            'Incluir resumen estadístico' => 'Include statistical summary',
            'Incluir detalles de eventos' => 'Include event details',
            'Configuración de reportes guardada correctamente.' => 'Report configuration saved successfully.',
            'Sistema de auditoría configurado exitosamente. El monitoreo está activo.' => 'Audit system configured successfully. Monitoring is active.',
            'Se eliminaron %d triggers correctamente.' => '%d triggers removed successfully.',
            'Reporte diario activado.' => 'Daily report activated.',
            'Reporte diario desactivado.' => 'Daily report deactivated.',
            'No tienes permisos para realizar esta acción.' => 'You do not have permissions to perform this action.',
            'Configuración de Mailjet' => 'Mailjet Configuration',
            'No configurada' => 'Not configured',
            'Total Eventos' => 'Total Events',
            'Enviar Reporte Ahora' => 'Send Report Now',
            'Vista Previa' => 'Preview',
            'Estado del Sistema de Reportes' => 'Report System Status',
            'Registro completo de cambios detectados en las tablas monitoreadas con información de usuario WordPress.' => 'Complete record of changes detected in monitored tables with WordPress user information.',
            'Usuario WordPress:' => 'WordPress User:',
            'Tabla:' => 'Table:',
            'Todos los usuarios' => 'All users',
            'Todas las acciones' => 'All actions',
            'Todas las tablas' => 'All tables',
            'Filtrar' => 'Filter',
            'Limpiar' => 'Clear',
            'Mostrando' => 'Showing',
            'de' => 'of',
            'registros' => 'records',
            'No se encontraron logs con los filtros aplicados.' => 'No logs found with the applied filters.',
            'Próximo Envío:' => 'Next Send:',
            'No programado' => 'Not scheduled',
            'Último Envío:' => 'Last Send:',
            'Nunca' => 'Never',
            'Total Destinatarios:' => 'Total Recipients:',
            'Activados' => 'Enabled',
            'Destinatarios (separados por comas):' => 'Recipients (comma separated):',
            'Contenido del reporte:' => 'Report content:',
            'Tablas monitoreadas:' => 'Monitored tables:',
            'Eventos detectados:' => 'Detected events:',
            'Datos capturados:' => 'Captured data:',
            'Usuario, timestamp, cambios' => 'User, timestamp, changes',
            'Mostrando %d de %s registros' => 'Showing %d of %s records',
            'Total Eventos' => 'Total Events',
            'Actualizaciones' => 'Updates', 
            'Eliminaciones' => 'Deletions',
            'Enviar Reporte Ahora' => 'Send Report Now',
            'Vista Previa' => 'Preview',
            'Enable automatic reports' => 'Enable automatic reports',
            'Frecuencia:' => 'Frequency:',
            'Diario' => 'Daily',
            'Send time:' => 'Send time:',
            'Destinatarios (separados por comas):' => 'Recipients (comma separated):',
            'Contenido del reporte:' => 'Report content:',
            'Include statistical summary' => 'Include statistical summary',
            'Include event details' => 'Include event details',
            'Save Configuration' => 'Save Configuration',
            'Report System Status' => 'Report System Status',
            'Automatic Reports:' => 'Automatic Reports:',
            'Activados' => 'Enabled',
            'Next Send:' => 'Next Send:',
            'No programado' => 'Not scheduled',
            'Last Send:' => 'Last Send:',
            'Nunca' => 'Never',
            'Total Recipients:' => 'Total Recipients:',
            'Configuration Status' => 'Configuration Status',
            'No configurada' => 'Not configured',
            'Opcional' => 'Optional', 
            'configurados' => 'configured',
            'Configuración Incompleta' => 'Incomplete Configuration',
            'Complete la configuración para enviar reportes' => 'Complete configuration to send reports',
            'Vista Previa del Reporte' => 'Report Preview',
            'Triggers Configurados' => 'Configured Triggers',
            'Nombre del Trigger' => 'Trigger Name',
            'Evento' => 'Event',
            'Timing' => 'Timing',
            'Estado' => 'Status',
            'Activo' => 'Active',
            'Configura el sistema de reportes por correo usando Mailjet API.' => 'Configure the email report system using Mailjet API.',
            'Credenciales de API' => 'API Credentials',
            'Ingresa tu API Key de Mailjet' => 'Enter your Mailjet API Key',
            'Ingresa tu Secret Key de Mailjet' => 'Enter your Mailjet Secret Key',
            'Email del remitente desde el cual se enviarán los reportes' => 'Sender email from which reports will be sent',
            'Nombre que aparecerá como remitente' => 'Name that will appear as sender',
            'Destinatarios del reporte' => 'Report Recipients',
            'Configuración de estado' => 'Configuration Status',
            'No configurado' => 'Not configured',
            'Configuración incompleta' => 'Incomplete Configuration',
            'Completa la configuración para enviar reportes' => 'Complete configuration to send reports',
            'Configuración de reportes' => 'Report Configuration',
            'Habilitar reportes automáticos' => 'Enable automatic reports',
            'Frecuencia' => 'Frequency',
            'Hora de envío' => 'Send time',
            'Contenido del reporte:' => 'Report content:',
            'Incluir resumen estadístico' => 'Include statistical summary',
            'Incluir detalles del evento' => 'Include event details',
            'Guardar configuración' => 'Save Configuration'
        );
        
        if (isset($translations[$text])) {
            return $translations[$text];
        }
    }
    
    // Para español u otros idiomas, devolver el texto original
    return $translation;
}

/**
 * Traducciones de fallback con contexto
 */
function dbst_fallback_translations($translation, $text, $context, $domain) {
    if ($domain !== 'db-safetrigger') {
        return $translation;
    }
    
    // Usar la misma lógica que la función sin contexto
    return dbst_fallback_translations_simple($translation, $text, $domain);
}

// Definir constantes
define('DB_SAFETRIGGER_VERSION', '1.1.0');
define('DB_SAFETRIGGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DB_SAFETRIGGER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Cargar traducciones inmediatamente
dbst_load_textdomain();

/**
 * Obtener nombre completo de la tabla de auditoría con prefijo de WordPress
 *
 * @return string Nombre completo de la tabla
 */
function dbst_get_audit_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'BD_SafeTrigger';
}

// Hooks de activación
register_activation_hook(__FILE__, 'dbst_activate_plugin');
register_deactivation_hook(__FILE__, 'dbst_deactivate_plugin');

/**
 * Activación del plugin
 */
function dbst_activate_plugin() {
    $activator_file = plugin_dir_path(__FILE__) . 'includes/class-db-safetrigger-activator.php';
    if (file_exists($activator_file)) {
        require_once $activator_file;
        if (class_exists('DB_SafeTrigger_Activator')) {
            DB_SafeTrigger_Activator::activate();
        }
    }
    
    // Fallback: crear tabla básica si no existe activador
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = dbst_get_audit_table_name();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        db_user VARCHAR(128),
        wp_user_id BIGINT UNSIGNED NULL,
        table_name VARCHAR(128) NOT NULL,
        action ENUM('UPDATE','DELETE') NOT NULL,
        pk_value VARCHAR(64) NOT NULL,
        old_data LONGTEXT,
        client_host VARCHAR(255),
        PRIMARY KEY (id),
        KEY idx_table_time (table_name, event_time),
        KEY idx_action_time (action, event_time),
        KEY idx_wp_user_time (wp_user_id, event_time)
    ) ENGINE=InnoDB $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Migrar datos de tabla antigua si existe
    dbst_migrate_old_table();
    
    // Configuraciones básicas
    add_option('db_safetrigger_version', '1.1.0');
    add_option('dbst_daily_report_enabled', 1);
}

/**
 * Desactivación del plugin
 */
function dbst_deactivate_plugin() {
    wp_clear_scheduled_hook('dbst_daily_audit_report');
}

// Agregar menú de administración
add_action('admin_menu', function() {
    // Asegurar que las traducciones estén cargadas
    dbst_load_textdomain();
    
    add_options_page(
        __('DB-SafeTrigger', 'db-safetrigger'),
        __('DB-SafeTrigger', 'db-safetrigger'),
        'manage_options',
        'db-safetrigger',
        'dbst_admin_page'
    );
});

// Hook para reporte diario
add_action('dbst_daily_audit_report', 'dbst_send_daily_report');

/**
 * Página de administración principal
 */
function dbst_admin_page() {
    // Asegurar que las traducciones estén cargadas antes de mostrar la página
    dbst_load_textdomain();
    
    global $wpdb;
    
    $message = '';
    $message_type = '';
    
    // Procesar acciones
    if (isset($_GET['action']) && wp_verify_nonce($_GET['_wpnonce'], 'dbst_action')) {
        switch ($_GET['action']) {
            case 'create_triggers':
                $result = dbst_create_triggers_definitivo();
                $message = $result['message'];
                $message_type = $result['type'];
                break;
            
            case 'delete_triggers':
                $triggers = $wpdb->get_results("SHOW TRIGGERS");
                $deleted_count = 0;
                
                foreach ($triggers as $trigger) {
                    if (strpos($trigger->Trigger, 'trg_') === 0) {
                        $wpdb->query("DROP TRIGGER IF EXISTS `{$trigger->Trigger}`");
                        $deleted_count++;
                    }
                }
                
                $message = sprintf(__('✅ Se eliminaron %d triggers correctamente.', 'db-safetrigger'), $deleted_count);
                $message_type = 'success';
                break;
                
            case 'save_mailjet':
                update_option('dbst_mailjet_api_key', sanitize_text_field($_POST['api_key']));
                update_option('dbst_mailjet_secret_key', sanitize_text_field($_POST['secret_key']));
                update_option('dbst_mailjet_from_email', sanitize_email($_POST['from_email']));
                update_option('dbst_mailjet_from_name', sanitize_text_field($_POST['from_name']));
                update_option('dbst_report_recipients', sanitize_textarea_field($_POST['recipients']));
                
                $message = __('✅ Configuración de Mailjet guardada correctamente.', 'db-safetrigger');
                $message_type = 'success';
                break;
                
            case 'toggle_daily_report':
                $current = get_option('dbst_daily_report_enabled', 1);
                $new_value = $current ? 0 : 1;
                update_option('dbst_daily_report_enabled', $new_value);
                
                if ($new_value) {
                    wp_schedule_event(time() + DAY_IN_SECONDS, 'daily', 'dbst_daily_audit_report');
                    $message = __('✅ Reporte diario activado.', 'db-safetrigger');
                } else {
                    wp_clear_scheduled_hook('dbst_daily_audit_report');
                    $message = __('⏸️ Reporte diario desactivado.', 'db-safetrigger');
                }
                $message_type = 'success';
                break;
                
            case 'save_report_config':
                if (current_user_can('manage_options')) {
                    $daily_enabled = isset($_POST['daily_enabled']) ? 1 : 0;
                    $frequency = sanitize_text_field($_POST['report_frequency']);
                    $time = sanitize_text_field($_POST['report_time']);
                    $recipients = sanitize_textarea_field($_POST['report_recipients']);
                    $include_summary = isset($_POST['include_summary']) ? 1 : 0;
                    $include_details = isset($_POST['include_details']) ? 1 : 0;
                    
                    update_option('dbst_daily_report_enabled', $daily_enabled);
                    update_option('dbst_report_frequency', $frequency);
                    update_option('dbst_report_time', $time);
                    update_option('dbst_report_recipients', $recipients);
                    update_option('dbst_report_include_summary', $include_summary);
                    update_option('dbst_report_include_details', $include_details);
                    
                    // Reprogramar el cron si está activado
                    wp_clear_scheduled_hook('dbst_daily_audit_report');
                    if ($daily_enabled) {
                        $schedule_time = strtotime("today $time");
                        if ($schedule_time < time()) {
                            $schedule_time += DAY_IN_SECONDS; // Programar para mañana
                        }
                        wp_schedule_event($schedule_time, $frequency, 'dbst_daily_audit_report');
                    }
                    
                    $message = __('✅ Configuración de reportes guardada correctamente.', 'db-safetrigger');
                    $message_type = 'success';
                } else {
                    $message = __('❌ No tienes permisos para realizar esta acción.', 'db-safetrigger');
                    $message_type = 'error';
                }
                break;
                
            case 'send_instant_report':
                if (current_user_can('manage_options')) {
                    $result = dbst_send_instant_audit_report();
                    $message = $result['message'];
                    $message_type = $result['success'] ? 'success' : 'error';
                } else {
                    $message = __('❌ No tienes permisos para realizar esta acción.', 'db-safetrigger');
                    $message_type = 'error';
                }
                break;
        }
    }
    
    // Mostrar mensaje de resultado
    if ($message) {
        $class = ($message_type === 'error') ? 'notice-error' : 'notice-success';
        echo "<div class='notice $class is-dismissible'><p>$message</p></div>";
    }
    
    // Determinar la pestaña activa
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'status';
    
    ?>
    <div class="wrap">
        <h1><?php echo '🛡️ ' . __('DB-SafeTrigger v1.1.0', 'db-safetrigger'); ?></h1>
        <p><strong><?php _e('Sistema de Auditoría y Trazabilidad de Base de Datos para WordPress', 'db-safetrigger'); ?></strong></p>
        
        <h2 class="nav-tab-wrapper">
            <a href="?page=db-safetrigger&tab=status" class="nav-tab <?php echo $active_tab == 'status' ? 'nav-tab-active' : ''; ?>"><?php _e('Estado del Sistema', 'db-safetrigger'); ?></a>
            <a href="?page=db-safetrigger&tab=triggers" class="nav-tab <?php echo $active_tab == 'triggers' ? 'nav-tab-active' : ''; ?>"><?php _e('Gestión de Triggers', 'db-safetrigger'); ?></a>
            <a href="?page=db-safetrigger&tab=mailjet" class="nav-tab <?php echo $active_tab == 'mailjet' ? 'nav-tab-active' : ''; ?>"><?php _e('Configuración Mailjet', 'db-safetrigger'); ?></a>
            <a href="?page=db-safetrigger&tab=reports" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : ''; ?>"><?php _e('Reportes', 'db-safetrigger'); ?></a>
            <a href="?page=db-safetrigger&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>"><?php _e('Logs de Auditoría', 'db-safetrigger'); ?></a>
        </h2>
        
        <?php
        switch($active_tab) {
            case 'triggers':
                $nonce = wp_create_nonce('dbst_action');
                dbst_triggers_tab($nonce);
                break;
            case 'mailjet':
                dbst_mailjet_tab();
                break;
            case 'reports':
                $nonce = wp_create_nonce('dbst_action');
                dbst_reports_tab($nonce);
                break;
            case 'logs':
                dbst_logs_tab();
                break;
            default:
                dbst_status_tab();
        }
        ?>
    </div>
    
    <style>
        .dbst-card {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #2271b1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .dbst-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .dbst-stat-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .dbst-stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2271b1;
            display: block;
        }
    </style>
    <?php
}

/**
 * Pestaña de estado del sistema
 */
function dbst_status_tab() {
    global $wpdb;
    
    // Verificar tabla de auditoría
    $audit_table = dbst_get_audit_table_name();
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $audit_table)) === $audit_table;
    
    // Estadísticas
    $total_logs = $table_exists ? $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table`") : 0;
    $logs_today = $table_exists ? $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table` WHERE DATE(event_time) = CURDATE()") : 0;
    
    // Verificar triggers
    $triggers = $wpdb->get_results("SHOW TRIGGERS");
    $db_triggers = array_filter($triggers, function($t) { return strpos($t->Trigger, 'trg_') === 0; });
    $trigger_count = count($db_triggers);
    
    ?>
    <div class="dbst-card">
        <h2><?php echo '📊 ' . __('Estado del Sistema', 'db-safetrigger'); ?></h2>
        <p><?php _e('Información general sobre el estado del sistema de auditoría.', 'db-safetrigger'); ?></p>
        
        <div class="dbst-stat-grid">
            <div class="dbst-stat-box">
                <span class="dbst-stat-number"><?php echo $table_exists ? '✅' : '❌'; ?></span>
                <strong><?php _e('Tabla de Auditoría', 'db-safetrigger'); ?></strong><br>
                <small><?php echo $table_exists ? __('Configurada correctamente', 'db-safetrigger') : __('No encontrada', 'db-safetrigger'); ?></small>
            </div>
            
            <div class="dbst-stat-box">
                <span class="dbst-stat-number"><?php echo $trigger_count; ?></span>
                <strong><?php _e('Triggers Activos', 'db-safetrigger'); ?></strong><br>
                <small><?php echo $trigger_count > 0 ? __('Sistema funcionando', 'db-safetrigger') : __('Triggers no configurados', 'db-safetrigger'); ?></small>
            </div>
            
            <div class="dbst-stat-box">
                <span class="dbst-stat-number"><?php echo number_format($total_logs); ?></span>
                <strong><?php _e('Total de Logs', 'db-safetrigger'); ?></strong><br>
                <small><?php _e('Desde el inicio', 'db-safetrigger'); ?></small>
            </div>
            
            <div class="dbst-stat-box">
                <span class="dbst-stat-number"><?php echo number_format($logs_today); ?></span>
                <strong><?php _e('Logs Hoy', 'db-safetrigger'); ?></strong><br>
                <small><?php echo date('Y-m-d'); ?></small>
            </div>
        </div>
        
        <?php if ($trigger_count > 0): ?>
            <h3><?php echo '🔧 ' . __('Triggers Configurados', 'db-safetrigger'); ?></h3>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 6px;">
                <?php foreach ($db_triggers as $trigger): ?>
                    <div style="margin: 5px 0; padding: 8px; background: white; border-radius: 3px;">
                        <strong><?php echo esc_html($trigger->Trigger); ?></strong> - 
                        <?php _e('Tabla:', 'db-safetrigger'); ?> <?php echo esc_html($trigger->Table); ?> - 
                        <?php _e('Evento:', 'db-safetrigger'); ?> <?php echo esc_html($trigger->Event); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
    <?php
}

/**
 * Pestaña de gestión de triggers
 */
function dbst_triggers_tab($nonce) {
    global $wpdb;
    
    // Verificar triggers existentes
    $triggers = $wpdb->get_results("SHOW TRIGGERS");
    $db_triggers = array_filter($triggers, function($t) { return strpos($t->Trigger, 'trg_') === 0; });
    
    // Obtener estadísticas de tablas monitoreadas
    $audit_table = dbst_get_audit_table_name();
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $audit_table)) === $audit_table;
    
    $monitored_tables = array('posts', 'users', 'comments');
    $table_stats = array();
    
    foreach ($monitored_tables as $table) {
        $prefixed_table = $wpdb->prefix . $table;
        $table_stats[$table] = array(
            'exists' => $wpdb->get_var("SHOW TABLES LIKE '$prefixed_table'") === $prefixed_table,
            'triggers' => 0,
            'events_today' => 0
        );
        
        // Contar triggers para esta tabla
        foreach ($db_triggers as $trigger) {
            if (strpos($trigger->Trigger, "trg_{$table}_") === 0) {
                $table_stats[$table]['triggers']++;
            }
        }
        
        // Contar eventos de hoy
        if ($table_exists) {
            $table_stats[$table]['events_today'] = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM `$audit_table` 
                WHERE table_name LIKE '%{$table}%' 
                AND DATE(event_time) = CURDATE()
            ");
        }
    }
    
    ?>
    <div class="dbst-card">
        <h2><?php echo '🔧 ' . __('Gestión de Triggers de Auditoría', 'db-safetrigger'); ?></h2>
        <p><?php _e('Administra y monitorea los triggers automáticos para la trazabilidad de datos.', 'db-safetrigger'); ?></p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
            <!-- Panel de Control -->
            <div style="background: #f9f9f9; padding: 20px; border-radius: 6px;">
                <h3 style="margin-top: 0;"><?php echo '⚡ ' . __('Control de Triggers', 'db-safetrigger'); ?></h3>
                
                <div style="margin: 15px 0;">
                    <a href="?page=db-safetrigger&tab=triggers&action=create_triggers&_wpnonce=<?php echo $nonce; ?>" 
                       class="button button-primary" style="margin-right: 10px;">
                        <?php echo '🚀 ' . __('Crear/Actualizar Triggers', 'db-safetrigger'); ?>
                    </a>
                    
                    <?php if (count($db_triggers) > 0): ?>
                        <a href="?page=db-safetrigger&tab=triggers&action=delete_triggers&_wpnonce=<?php echo $nonce; ?>" 
                           class="button button-secondary"
                           onclick="return confirm('¿Estás seguro de eliminar todos los triggers?\n\nEsta acción detendrá el monitoreo de auditoría.')">
                            <?php echo '🗑️ ' . __('Eliminar Todos', 'db-safetrigger'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <div style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-top: 15px;">
                    <h4 style="margin: 0 0 10px 0; color: #1976d2;">📋 <?php _e('Información', 'db-safetrigger'); ?></h4>
                    <ul style="margin: 0; padding-left: 20px; color: #555;">
                        <li><strong><?php _e('Tablas monitoreadas:', 'db-safetrigger'); ?></strong> posts, users, comments</li>
                        <li><strong><?php _e('Eventos detectados:', 'db-safetrigger'); ?></strong> <?php _e('UPDATE y DELETE', 'db-safetrigger'); ?></li>
                        <li><strong><?php _e('Datos capturados:', 'db-safetrigger'); ?></strong> <?php _e('Usuario, timestamp, cambios', 'db-safetrigger'); ?></li>
                    </ul>
                </div>
            </div>

            <!-- Panel de Estadísticas -->
            <div style="background: #f9f9f9; padding: 20px; border-radius: 6px;">
                <h3 style="margin-top: 0;"><?php echo '📊 ' . __('Estado de Monitoreo', 'db-safetrigger'); ?></h3>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 15px 0;">
                    <?php foreach ($monitored_tables as $table): 
                        $stats = $table_stats[$table];
                        $is_active = $stats['exists'] && $stats['triggers'] >= 2;
                        
                        $table_icons = array(
                            'posts' => '📝',
                            'users' => '👥', 
                            'comments' => '💬'
                        );
                    ?>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center; border: 2px solid <?php echo $is_active ? '#28a745' : '#dc3545'; ?>;">
                            <div style="font-size: 20px; margin-bottom: 5px;">
                                <?php echo $table_icons[$table]; ?>
                            </div>
                            <div style="font-weight: 600; text-transform: capitalize; font-size: 12px;">
                                <?php echo $table; ?>
                            </div>
                            <div style="font-size: 10px; color: #666; margin: 3px 0;">
                                <?php echo $stats['triggers']; ?>/2 triggers
                            </div>
                            <div style="font-size: 11px; font-weight: 600; color: <?php echo $is_active ? '#28a745' : '#dc3545'; ?>;">
                                <?php echo $is_active ? '✅ ' . __('Activo', 'db-safetrigger') : '❌ ' . __('Inactivo', 'db-safetrigger'); ?>
                            </div>
                            <?php if ($stats['events_today'] > 0): ?>
                                <div style="font-size: 9px; color: #007cba; margin-top: 3px;">
                                    <?php echo $stats['events_today']; ?> eventos hoy
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Tabla de Triggers -->
        <?php if (count($db_triggers) > 0): ?>
            <h3><?php echo '📋 ' . __('Triggers Configurados', 'db-safetrigger'); ?></h3>
            <div style="background: white; border: 1px solid #ddd; border-radius: 4px;">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 35%;"><?php _e('Nombre del Trigger', 'db-safetrigger'); ?></th>
                            <th style="width: 20%;"><?php _e('Tabla', 'db-safetrigger'); ?></th>
                            <th style="width: 20%;"><?php _e('Evento', 'db-safetrigger'); ?></th>
                            <th style="width: 15%;"><?php _e('Timing', 'db-safetrigger'); ?></th>
                            <th style="width: 10%;"><?php _e('Estado', 'db-safetrigger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($db_triggers as $trigger): ?>
                            <tr>
                                <td>
                                    <code style="background: #f1f3f4; padding: 4px 6px; border-radius: 3px; font-size: 12px;">
                                        <?php echo esc_html($trigger->Trigger); ?>
                                    </code>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($trigger->Table); ?></strong>
                                </td>
                                <td>
                                    <span style="padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; background: <?php echo $trigger->Event === 'UPDATE' ? '#e3f2fd' : '#ffebee'; ?>; color: <?php echo $trigger->Event === 'UPDATE' ? '#1976d2' : '#d32f2f'; ?>;">
                                        <?php echo $trigger->Event === 'UPDATE' ? '✏️ UPDATE' : '🗑️ DELETE'; ?>
                                    </span>
                                </td>
                                <td style="font-family: monospace; color: #666; font-size: 12px;">
                                    <?php echo esc_html($trigger->Timing); ?>
                                </td>
                                <td>
                                    <span style="color: #28a745; font-weight: 600; font-size: 12px;">
                                        <?php echo '✅ ' . __('Activo', 'db-safetrigger'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 6px;">
                <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
                <h3 style="color: #ff9800; margin-bottom: 15px;"><?php _e('No hay triggers configurados', 'db-safetrigger'); ?></h3>
                <p style="color: #666; margin-bottom: 25px;">
                    <?php _e('El sistema de auditoría no está activo. Configura los triggers para comenzar el monitoreo.', 'db-safetrigger'); ?>
                </p>
                <a href="?page=db-safetrigger&tab=triggers&action=create_triggers&_wpnonce=<?php echo $nonce; ?>" 
                   class="button button-primary">
                    <?php echo '🚀 ' . __('Configurar Triggers Ahora', 'db-safetrigger'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Pestaña de configuración Mailjet
 */
function dbst_mailjet_tab() {
    if (isset($_POST['save_mailjet']) && wp_verify_nonce($_POST['_wpnonce'], 'dbst_mailjet')) {
        update_option('dbst_mailjet_api_key', sanitize_text_field($_POST['api_key']));
        update_option('dbst_mailjet_secret_key', sanitize_text_field($_POST['secret_key']));
        update_option('dbst_mailjet_from_email', sanitize_email($_POST['from_email']));
        update_option('dbst_mailjet_from_name', sanitize_text_field($_POST['from_name']));
        update_option('dbst_report_recipients', sanitize_textarea_field($_POST['recipients']));
        
        echo '<div class="notice notice-success is-dismissible"><p>✅ ' . __('Configuración guardada correctamente.', 'db-safetrigger') . '</p></div>';
    }
    
    $api_key = get_option('dbst_mailjet_api_key', '');
    $secret_key = get_option('dbst_mailjet_secret_key', '');
    $from_email = get_option('dbst_mailjet_from_email', '');
    $from_name = get_option('dbst_mailjet_from_name', '');
    $recipients = get_option('dbst_report_recipients', '');
    
    // Verificar configuración
    $has_config = !empty($api_key) && !empty($secret_key) && !empty($from_email);
    $recipient_count = count(array_filter(array_map('trim', explode("\n", $recipients))));
    
    ?>
    <div class="dbst-card">
        <h2><?php echo '📧 ' . __('Configuración de Mailjet', 'db-safetrigger'); ?></h2>
        <p><?php _e('Configura el sistema de envío de reportes por email usando Mailjet API.', 'db-safetrigger'); ?></p>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin: 20px 0;">
            <!-- Panel de Configuración -->
            <div style="background: #f9f9f9; padding: 20px; border-radius: 6px;">
                <h3 style="margin-top: 0;"><?php echo '⚙️ ' . __('Credenciales de API', 'db-safetrigger'); ?></h3>
                
                <form method="post">
                    <?php wp_nonce_field('dbst_mailjet'); ?>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="api_key" style="display: block; font-weight: 600; margin-bottom: 5px;"><?php echo '🔑 ' . __('API Key', 'db-safetrigger'); ?></label>
                        <input type="text" 
                               id="api_key" 
                               name="api_key" 
                               value="<?php echo esc_attr($api_key); ?>" 
                               placeholder="<?php _e('Ingresa tu API Key de Mailjet', 'db-safetrigger'); ?>"
                               style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" />
                        <small style="color: #666;"><?php _e('Obtén tu API Key desde tu panel de Mailjet', 'db-safetrigger'); ?></small>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="secret_key" style="display: block; font-weight: 600; margin-bottom: 5px;"><?php echo '🔐 ' . __('Secret Key', 'db-safetrigger'); ?></label>
                        <input type="password" 
                               id="secret_key" 
                               name="secret_key" 
                               value="<?php echo esc_attr($secret_key); ?>" 
                               placeholder="<?php _e('Ingresa tu Secret Key de Mailjet', 'db-safetrigger'); ?>"
                               style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" />
                        <small style="color: #666;"><?php _e('Tu Secret Key privada (mantenida en secreto)', 'db-safetrigger'); ?></small>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="from_email" style="display: block; font-weight: 600; margin-bottom: 5px;"><?php echo '📤 ' . __('Email Remitente', 'db-safetrigger'); ?></label>
                        <input type="email" 
                               id="from_email" 
                               name="from_email" 
                               value="<?php echo esc_attr($from_email); ?>" 
                               placeholder="noreply@tudominio.com"
                               style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" />
                        <small style="color: #666;"><?php _e('Email desde el cual se enviarán los reportes', 'db-safetrigger'); ?></small>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="from_name" style="display: block; font-weight: 600; margin-bottom: 5px;"><?php echo '👤 ' . __('Nombre del Remitente', 'db-safetrigger'); ?></label>
                        <input type="text" 
                               id="from_name" 
                               name="from_name" 
                               value="<?php echo esc_attr($from_name); ?>" 
                               placeholder="DB-SafeTrigger"
                               style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" />
                        <small style="color: #666;"><?php _e('Nombre que aparecerá como remitente', 'db-safetrigger'); ?></small>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label for="recipients" style="display: block; font-weight: 600; margin-bottom: 5px;"><?php echo '📋 ' . __('Destinatarios de Reportes', 'db-safetrigger'); ?></label>
                        <textarea id="recipients" 
                                  name="recipients" 
                                  rows="4" 
                                  placeholder="admin@tudominio.com&#10;usuario@tudominio.com&#10;otro@tudominio.com"
                                  style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;"><?php echo esc_textarea($recipients); ?></textarea>
                        <small style="color: #666;"><?php _e('Un email por línea. Estos usuarios recibirán los reportes automáticos', 'db-safetrigger'); ?></small>
                    </div>

                    <button type="submit" name="save_mailjet" class="button button-primary">
                        <?php echo '💾 ' . __('Guardar Configuración', 'db-safetrigger'); ?>
                    </button>
                </form>
            </div>

            <!-- Panel de Estado -->
            <div style="background: #f9f9f9; padding: 20px; border-radius: 6px;">
                <h3 style="margin-top: 0;">📊 <?php _e('Estado de la Configuración', 'db-safetrigger'); ?></h3>
                
                <div style="margin-bottom: 15px;">
                    <div style="background: <?php echo !empty($api_key) ? '#e8f5e8' : '#f8e8e8'; ?>; padding: 15px; border-radius: 6px; text-align: center; border: 2px solid <?php echo !empty($api_key) ? '#28a745' : '#dc3545'; ?>;">
                        <div style="font-size: 20px; margin-bottom: 5px;">
                            <?php echo !empty($api_key) ? '✅' : '❌'; ?>
                        </div>
                        <div style="font-weight: 600; font-size: 14px;">API Key</div>
                        <div style="font-size: 11px; color: #666; margin-top: 3px;">
                            <?php echo !empty($api_key) ? __('Configurada', 'db-safetrigger') : __('No configurada', 'db-safetrigger'); ?>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <div style="background: <?php echo !empty($secret_key) ? '#e8f5e8' : '#f8e8e8'; ?>; padding: 15px; border-radius: 6px; text-align: center; border: 2px solid <?php echo !empty($secret_key) ? '#28a745' : '#dc3545'; ?>;">
                        <div style="font-size: 20px; margin-bottom: 5px;">
                            <?php echo !empty($secret_key) ? '🔐' : '❌'; ?>
                        </div>
                        <div style="font-weight: 600; font-size: 14px;">Secret Key</div>
                        <div style="font-size: 11px; color: #666; margin-top: 3px;">
                            <?php echo !empty($secret_key) ? __('Configurada', 'db-safetrigger') : __('No configurada', 'db-safetrigger'); ?>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <div style="background: <?php echo !empty($from_email) ? '#e8f5e8' : '#fff8e1'; ?>; padding: 15px; border-radius: 6px; text-align: center; border: 2px solid <?php echo !empty($from_email) ? '#28a745' : '#ffc107'; ?>;">
                        <div style="font-size: 20px; margin-bottom: 5px;">
                            <?php echo !empty($from_email) ? '📤' : '⚠️'; ?>
                        </div>
                        <div style="font-weight: 600; font-size: 14px;">Email Remitente</div>
                        <div style="font-size: 11px; color: #666; margin-top: 3px;">
                            <?php echo !empty($from_email) ? __('Configurado', 'db-safetrigger') : __('Opcional', 'db-safetrigger'); ?>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="background: <?php echo $recipient_count > 0 ? '#e8f5e8' : '#fff8e1'; ?>; padding: 15px; border-radius: 6px; text-align: center; border: 2px solid <?php echo $recipient_count > 0 ? '#28a745' : '#ffc107'; ?>;">
                        <div style="font-size: 20px; margin-bottom: 5px;">
                            📋
                        </div>
                        <div style="font-weight: 600; font-size: 14px;">Destinatarios</div>
                        <div style="font-size: 11px; color: #666; margin-top: 3px;">
                            <?php echo $recipient_count; ?> <?php _e('configurados', 'db-safetrigger'); ?>
                        </div>
                    </div>
                </div>

                <div style="padding: 15px; background: <?php echo $has_config ? '#e8f5e8' : '#f8e8e8'; ?>; border-radius: 6px; text-align: center; border: 2px solid <?php echo $has_config ? '#28a745' : '#dc3545'; ?>;">
                    <div style="font-weight: 600; color: <?php echo $has_config ? '#28a745' : '#dc3545'; ?>;">
                        <?php echo $has_config ? '✅ ' . __('Sistema Listo', 'db-safetrigger') : '❌ ' . __('Configuración Incompleta', 'db-safetrigger'); ?>
                    </div>
                    <div style="font-size: 11px; color: #666; margin-top: 3px;">
                        <?php echo $has_config ? __('Los reportes se pueden enviar', 'db-safetrigger') : __('Complete la configuración para enviar reportes', 'db-safetrigger'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Pestaña de reportes
 */
function dbst_reports_tab($nonce) {
    global $wpdb;
    
    $daily_report_enabled = get_option('dbst_daily_report_enabled', 1);
    $report_frequency = get_option('dbst_report_frequency', 'daily');
    $report_recipients = get_option('dbst_report_recipients', get_option('admin_email'));
    $include_summary = get_option('dbst_report_include_summary', 1);
    $include_details = get_option('dbst_report_include_details', 1);
    $report_time = get_option('dbst_report_time', '08:00');
    
    // Obtener estadísticas de hoy
    $audit_table = dbst_get_audit_table_name();
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $audit_table)) === $audit_table;
    
    $stats_today = array('total' => 0, 'updates' => 0, 'deletes' => 0);
    if ($table_exists) {
        $stats_today['total'] = $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table` WHERE DATE(event_time) = CURDATE()");
        $stats_today['updates'] = $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table` WHERE action = 'UPDATE' AND DATE(event_time) = CURDATE()");
        $stats_today['deletes'] = $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table` WHERE action = 'DELETE' AND DATE(event_time) = CURDATE()");
    }
    
    ?>
    <div class="dbst-reports-container">
        <style>
        .dbst-reports-container {
            max-width: 1200px;
        }
        .dbst-reports-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .dbst-report-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #2271b1;
        }
        .dbst-report-card h3 {
            margin-top: 0;
            color: #2271b1;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dbst-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 15px 0;
        }
        .dbst-stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .dbst-stat-number {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #2271b1;
        }
        .dbst-form-row {
            margin-bottom: 15px;
        }
        .dbst-form-row label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .dbst-form-row input, .dbst-form-row select, .dbst-form-row textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        /* Estilos específicos para checkboxes - sobrescribir todo */
        .dbst-form-row .dbst-checkbox-group {
            background: none !important;
            border: none !important;
            padding: 4px 0 !important;
            margin-bottom: 4px !important;
        }
        .dbst-checkbox-group {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 6px;
            padding: 0;
            border: none;
            background: none;
        }
        .dbst-checkbox-group input[type="checkbox"] {
            margin: 0 !important;
            transform: scale(0.8);
            width: auto !important;
            padding: 0 !important;
            border: 1px solid #ccc !important;
            background: white !important;
        }
        .dbst-checkbox-group label {
            margin: 0 !important;
            font-size: 13px;
            font-weight: 500;
            padding: 0 !important;
            background: none !important;
            border: none !important;
            display: inline !important;
            width: auto !important;
        }
        .dbst-button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .dbst-send-now {
            background: #00a32a !important;
            border-color: #00a32a !important;
        }
        .dbst-send-now:hover {
            background: #008a20 !important;
            border-color: #008a20 !important;
        }
        .dbst-preview-btn {
            background: #0073aa !important;
            border-color: #0073aa !important;
        }
        </style>

        <h2><?php echo '📊 ' . __('Gestión de Reportes Avanzada', 'db-safetrigger'); ?></h2>
        <p><?php _e('Configura reportes automáticos personalizados y envía reportes instantáneos con los datos actuales.', 'db-safetrigger'); ?></p>

        <div class="dbst-reports-grid">
            <!-- Panel de Estadísticas Actuales -->
            <div class="dbst-report-card">
                <h3><?php echo '📊 ' . __('Estadísticas de Hoy', 'db-safetrigger'); ?></h3>
                <div class="dbst-stats-grid">
                    <div class="dbst-stat-item">
                        <span class="dbst-stat-number"><?php echo number_format($stats_today['total']); ?></span>
                        <small><?php _e('Total Eventos', 'db-safetrigger'); ?></small>
                    </div>
                    <div class="dbst-stat-item">
                        <span class="dbst-stat-number"><?php echo number_format($stats_today['updates']); ?></span>
                        <small><?php _e('Actualizaciones', 'db-safetrigger'); ?></small>
                    </div>
                    <div class="dbst-stat-item">
                        <span class="dbst-stat-number"><?php echo number_format($stats_today['deletes']); ?></span>
                        <small><?php _e('Eliminaciones', 'db-safetrigger'); ?></small>
                    </div>
                </div>
                
                <div class="dbst-button-group">
                    <a href="?page=db-safetrigger&tab=reports&action=send_instant_report&_wpnonce=<?php echo $nonce; ?>" 
                       class="button button-primary dbst-send-now">
                        <?php echo '📤 ' . __('Enviar Reporte Ahora', 'db-safetrigger'); ?>
                    </a>
                    <button type="button" id="preview-report" class="button dbst-preview-btn">
                        <?php echo '👁️ ' . __('Vista Previa', 'db-safetrigger'); ?>
                    </button>
                </div>
            </div>

            <!-- Panel de Configuración -->
            <div class="dbst-report-card">
                <h3><?php echo '⚙️ ' . __('Configuración de Reportes', 'db-safetrigger'); ?></h3>
                <form method="post" action="?page=db-safetrigger&tab=reports&action=save_report_config&_wpnonce=<?php echo $nonce; ?>">
                    
                    <div class="dbst-form-row">
                        <div class="dbst-checkbox-group">
                            <input type="checkbox" id="daily_enabled" name="daily_enabled" <?php checked($daily_report_enabled); ?>>
                            <label for="daily_enabled"><?php _e('Activar reportes automáticos', 'db-safetrigger'); ?></label>
                        </div>
                    </div>

                    <div class="dbst-form-row">
                        <label for="report_frequency">Frecuencia:</label>
                        <select name="report_frequency" id="report_frequency">
                            <option value="daily" <?php selected($report_frequency, 'daily'); ?>>Diario</option>
                            <option value="weekly" <?php selected($report_frequency, 'weekly'); ?>>Semanal</option>
                            <option value="monthly" <?php selected($report_frequency, 'monthly'); ?>>Mensual</option>
                        </select>
                    </div>

                    <div class="dbst-form-row">
                        <label for="report_time"><?php _e('Hora de envío:', 'db-safetrigger'); ?></label>
                        <input type="time" name="report_time" id="report_time" value="<?php echo esc_attr($report_time); ?>">
                    </div>

                    <div class="dbst-form-row">
                        <label for="report_recipients">Destinatarios (separados por comas):</label>
                        <textarea name="report_recipients" id="report_recipients" rows="3"><?php echo esc_textarea($report_recipients); ?></textarea>
                    </div>

                    <div class="dbst-form-row">
                        <label>Contenido del reporte:</label>
                        <div class="dbst-checkbox-group">
                            <input type="checkbox" id="include_summary" name="include_summary" <?php checked($include_summary); ?>>
                            <label for="include_summary"><?php _e('Incluir resumen estadístico', 'db-safetrigger'); ?></label>
                        </div>
                        <div class="dbst-checkbox-group">
                            <input type="checkbox" id="include_details" name="include_details" <?php checked($include_details); ?>>
                            <label for="include_details"><?php _e('Incluir detalles de eventos', 'db-safetrigger'); ?></label>
                        </div>
                    </div>

                    <button type="submit" class="button button-primary"><?php echo '💾 ' . __('Guardar Configuración', 'db-safetrigger'); ?></button>
                </form>
            </div>
        </div>

        <!-- Panel de Estado del Sistema -->
        <div class="dbst-report-card">
            <h3><?php echo '🔍 ' . __('Estado del Sistema de Reportes', 'db-safetrigger'); ?></h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="background: #f0f8f0; padding: 15px; border-radius: 6px;">
                    <strong><?php echo '📅 ' . __('Reportes Automáticos:', 'db-safetrigger'); ?></strong><br>
                    <span style="color: <?php echo $daily_report_enabled ? '#46b450' : '#dc3232'; ?>;">
                        <?php echo $daily_report_enabled ? '✅ Activados' : '❌ Desactivados'; ?>
                    </span>
                </div>
                
                <div style="background: #f0f8f0; padding: 15px; border-radius: 6px;">
                    <strong><?php echo '⏰ ' . __('Próximo Envío:', 'db-safetrigger'); ?></strong><br>
                    <?php 
                    $next_scheduled = wp_next_scheduled('dbst_daily_audit_report');
                    echo $next_scheduled ? date('Y-m-d H:i:s', $next_scheduled) : 'No programado';
                    ?>
                </div>
                
                <div style="background: #f0f8f0; padding: 15px; border-radius: 6px;">
                    <strong><?php echo '📧 ' . __('Último Envío:', 'db-safetrigger'); ?></strong><br>
                    <?php 
                    $last_sent = get_option('dbst_last_report_sent', 'Nunca');
                    echo $last_sent !== 'Nunca' ? date('Y-m-d H:i:s', strtotime($last_sent)) : $last_sent;
                    ?>
                </div>
                
                <div style="background: #f0f8f0; padding: 15px; border-radius: 6px;">
                    <strong><?php echo '📊 ' . __('Total Destinatarios:', 'db-safetrigger'); ?></strong><br>
                    <?php echo count(array_filter(array_map('trim', explode(',', $report_recipients)))); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Vista Previa -->
    <div id="report-preview-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 100000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 80%; max-height: 80%; overflow-y: auto;">
            <h3><?php echo '👁️ ' . __('Vista Previa del Reporte', 'db-safetrigger'); ?></h3>
            <div id="preview-content"></div>
            <button type="button" onclick="document.getElementById('report-preview-modal').style.display='none'" class="button">Cerrar</button>
        </div>
    </div>

    <script>
    document.getElementById('preview-report').addEventListener('click', function() {
        // Simulación de vista previa
        const previewContent = `
            <div style="font-family: Arial, sans-serif; line-height: 1.6;">
                <h2>📊 Reporte de Auditoría - ${new Date().toLocaleDateString()}</h2>
                <h3>📈 Resumen del Día</h3>
                <ul>
                    <li><strong>Total de eventos:</strong> <?php echo $stats_today['total']; ?></li>
                    <li><strong>Actualizaciones:</strong> <?php echo $stats_today['updates']; ?></li>
                    <li><strong>Eliminaciones:</strong> <?php echo $stats_today['deletes']; ?></li>
                </ul>
                <h3>🔍 Eventos Recientes</h3>
                <p><em>Los eventos más recientes serían listados aquí con fecha y hora detallada...</em></p>
                <hr>
                <p><small>Generado automáticamente por DB-SafeTrigger</small></p>
            </div>
        `;
        document.getElementById('preview-content').innerHTML = previewContent;
        document.getElementById('report-preview-modal').style.display = 'block';
    });

    // Cerrar modal al hacer clic fuera
    document.getElementById('report-preview-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
    </script>
    <?php
}

/**
 * Pestaña de logs de auditoría
 */
function dbst_logs_tab() {
    global $wpdb;
    
    // Verificar si la tabla existe
    $audit_table = dbst_get_audit_table_name();
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $audit_table)) === $audit_table;
    
    if (!$table_exists) {
        echo '<div class="dbst-card">';
        echo '<h2>❌ Tabla de Auditoría No Encontrada</h2>';
        echo "<p>La tabla $audit_table no existe. Por favor, activa el plugin o crea la tabla manualmente.</p>";
        echo '</div>';
        return;
    }
    
    // Filtros
    $user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $action_filter = isset($_GET['action_filter']) ? sanitize_text_field($_GET['action_filter']) : '';
    $table_filter = isset($_GET['table_filter']) ? sanitize_text_field($_GET['table_filter']) : '';
    
    // Paginación
    $per_page = 20;
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($page - 1) * $per_page;
    
    // Construir consulta
    $where_conditions = array();
    $where_params = array();
    
    if ($user_filter > 0) {
        $where_conditions[] = "l.wp_user_id = %d";
        $where_params[] = $user_filter;
    }
    
    if (!empty($action_filter)) {
        $where_conditions[] = "l.action = %s";
        $where_params[] = $action_filter;
    }
    
    if (!empty($table_filter)) {
        $where_conditions[] = "l.table_name LIKE %s";
        $where_params[] = '%' . $table_filter . '%';
    }
    
    $where_clause = !empty($where_conditions) ? implode(' AND ', $where_conditions) : '1=1';
    
    // Contar total de registros
    $count_query = "SELECT COUNT(*) FROM `$audit_table` l WHERE $where_clause";
    $total_logs = $wpdb->get_var(!empty($where_params) ? $wpdb->prepare($count_query, $where_params) : $count_query);
    
    // Obtener logs
    $query = "
        SELECT l.*, u.display_name, u.user_login
        FROM `$audit_table` l
        LEFT JOIN {$wpdb->users} u ON l.wp_user_id = u.ID
        WHERE $where_clause
        ORDER BY l.id DESC 
        LIMIT %d OFFSET %d
    ";
    
    $query_params = array_merge($where_params, array($per_page, $offset));
    $logs = $wpdb->get_results($wpdb->prepare($query, $query_params));
    
    // Obtener usuarios para filtro
    $users_with_logs = $wpdb->get_results("
        SELECT DISTINCT u.ID, u.user_login, u.display_name
        FROM `$audit_table` l
        INNER JOIN {$wpdb->users} u ON l.wp_user_id = u.ID
        ORDER BY u.display_name
    ");
    
    // Calcular número de páginas
    $total_pages = ceil($total_logs / $per_page);
    
    ?>
    <div class="dbst-card">
        <h2><?php echo '📜 ' . __('Logs de Auditoría', 'db-safetrigger'); ?></h2>
        <p><?php _e('Registro completo de cambios detectados en las tablas monitoreadas con información de usuario WordPress.', 'db-safetrigger'); ?></p>
        
        <!-- Filtros -->
        <div style="background: #f9f9f9; padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <form method="get" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                <input type="hidden" name="page" value="db-safetrigger">
                <input type="hidden" name="tab" value="logs">
                
                <div>
                    <label for="user_id"><strong><?php echo '👤 ' . __('Usuario WordPress:', 'db-safetrigger'); ?></strong></label><br>
                    <select name="user_id" id="user_id" style="min-width: 150px;">
                        <option value="0"><?php _e('Todos los usuarios', 'db-safetrigger'); ?></option>
                        <?php foreach($users_with_logs as $user): ?>
                            <option value="<?php echo $user->ID; ?>" <?php selected($user_filter, $user->ID); ?>>
                                <?php echo esc_html($user->display_name . ' (' . $user->user_login . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="action_filter"><strong><?php echo '🔄 ' . __('Acción:', 'db-safetrigger'); ?></strong></label><br>
                    <select name="action_filter" id="action_filter" style="min-width: 120px;">
                        <option value=""><?php _e('Todas las acciones', 'db-safetrigger'); ?></option>
                        <option value="UPDATE" <?php selected($action_filter, 'UPDATE'); ?>>UPDATE</option>
                        <option value="DELETE" <?php selected($action_filter, 'DELETE'); ?>>DELETE</option>
                    </select>
                </div>
                
                <div>
                    <label for="table_filter"><strong><?php echo '🗂️ ' . __('Tabla:', 'db-safetrigger'); ?></strong></label><br>
                    <select name="table_filter" id="table_filter" style="min-width: 120px;">
                        <option value=""><?php _e('Todas las tablas', 'db-safetrigger'); ?></option>
                        <option value="posts" <?php selected($table_filter, 'posts'); ?>><?php _e('Posts', 'db-safetrigger'); ?></option>
                        <option value="users" <?php selected($table_filter, 'users'); ?>><?php _e('Users', 'db-safetrigger'); ?></option>
                        <option value="comments" <?php selected($table_filter, 'comments'); ?>><?php _e('Comments', 'db-safetrigger'); ?></option>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="button button-primary"><?php echo '🔍 ' . __('Filtrar', 'db-safetrigger'); ?></button>
                    <a href="?page=db-safetrigger&tab=logs" class="button"><?php echo '🔄 ' . __('Limpiar', 'db-safetrigger'); ?></a>
                </div>
            </form>
        </div>
        
        <!-- Resultados -->
        <div style="background: white; border: 1px solid #ddd; border-radius: 4px;">
            <div style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #ddd;">
                <strong><?php echo '📊 ' . sprintf(__('Mostrando %d de %s registros', 'db-safetrigger'), count($logs), number_format($total_logs)); ?></strong>
                <?php if ($total_pages > 1): ?>
                    | Página <?php echo $page; ?> de <?php echo $total_pages; ?>
                <?php endif; ?>
            </div>
            
            <?php if (empty($logs)): ?>
                <div style="padding: 40px; text-align: center; color: #666;">
                    <?php echo '📭 ' . __('No se encontraron logs con los filtros aplicados.', 'db-safetrigger'); ?>
                </div>
            <?php else: ?>
                <style>
                .dbst-log-table {
                    border: none;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                }
                .dbst-log-table th {
                    background: #f8f9fa;
                    border-bottom: 2px solid #dee2e6;
                    font-weight: 600;
                    color: #495057;
                }
                .dbst-log-table td {
                    vertical-align: top;
                    padding: 12px 8px;
                    border-bottom: 1px solid #f1f3f4;
                }
                .dbst-log-table tr:hover {
                    background: #f8f9fa;
                }
                .dbst-datetime {
                    font-family: 'Courier New', monospace;
                    font-size: 12px;
                    background: #f8f9fa;
                    padding: 4px 8px;
                    border-radius: 4px;
                    border-left: 3px solid #2271b1;
                }
                .dbst-user-info {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .dbst-user-avatar {
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    background: #2271b1;
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 10px;
                    font-weight: bold;
                }
                .dbst-action-badge {
                    padding: 4px 8px;
                    border-radius: 12px;
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                }
                .dbst-action-update {
                    background: #e7f3ff;
                    color: #0066cc;
                    border: 1px solid #b3d9ff;
                }
                .dbst-action-delete {
                    background: #ffe7e7;
                    color: #cc0000;
                    border: 1px solid #ffb3b3;
                }
                .dbst-table-name {
                    font-family: 'Courier New', monospace;
                    background: #f1f3f4;
                    padding: 2px 6px;
                    border-radius: 3px;
                    font-size: 11px;
                    font-weight: 600;
                }
                </style>
                
                <table class="wp-list-table widefat fixed striped dbst-log-table">
                    <thead>
                        <tr>
                            <th style="width: 160px;">📅 Fecha y Hora</th>
                            <th style="width: 180px;">👤 Usuario WordPress</th>
                            <th style="width: 120px;">📋 Tabla</th>
                            <th style="width: 100px;">⚡ Acción</th>
                            <th style="width: 80px;">🔑 ID</th>
                            <th>🔧 Usuario BD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): 
                            $event_date = new DateTime($log->event_time);
                            $now = new DateTime();
                            $diff = $now->diff($event_date);
                            
                            // Determinar si es hoy, ayer, etc.
                            $date_label = '';
                            if ($diff->days == 0) {
                                $date_label = '🟢 Hoy';
                            } elseif ($diff->days == 1) {
                                $date_label = '🟡 Ayer';
                            } elseif ($diff->days <= 7) {
                                $date_label = '🔵 ' . $diff->days . ' días';
                            } else {
                                $date_label = '⚪ ' . $event_date->format('d/m/Y');
                            }
                        ?>
                            <tr>
                                <td>
                                    <div class="dbst-datetime">
                                        <div style="font-weight: 600; color: #2271b1; margin-bottom: 2px;">
                                            <?php echo $event_date->format('H:i:s'); ?>
                                        </div>
                                        <div style="font-size: 10px; color: #666;">
                                            <?php echo $date_label; ?> • <?php echo $event_date->format('d/m/Y'); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($log->wp_user_id && $log->display_name): ?>
                                        <div class="dbst-user-info">
                                            <div class="dbst-user-avatar">
                                                <?php echo strtoupper(substr($log->display_name, 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: #2271b1;">
                                                    <?php echo esc_html($log->display_name); ?>
                                                </div>
                                                <div style="font-size: 11px; color: #666;">
                                                    @<?php echo esc_html($log->user_login); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="dbst-user-info">
                                            <div class="dbst-user-avatar" style="background: #dc3545;">
                                                ⚠️
                                            </div>
                                            <div>
                                                <div style="color: #dc3545; font-weight: 600;">
                                                    Sistema
                                                </div>
                                                <div style="font-size: 11px; color: #666;">
                                                    No identificado
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dbst-table-name">
                                        <?php 
                                        $table_parts = explode('_', $log->table_name);
                                        $table_icon = '';
                                        $table_short = end($table_parts);
                                        
                                        switch($table_short) {
                                            case 'posts': $table_icon = '📝'; break;
                                            case 'users': $table_icon = '👥'; break;
                                            case 'comments': $table_icon = '💬'; break;
                                            default: $table_icon = '📋'; break;
                                        }
                                        
                                        echo $table_icon . ' ' . esc_html($table_short); 
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($log->action === 'UPDATE'): ?>
                                        <div class="dbst-action-badge dbst-action-update">
                                            ✏️ UPDATE
                                        </div>
                                    <?php else: ?>
                                        <div class="dbst-action-badge dbst-action-delete">
                                            🗑️ DELETE
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-family: 'Courier New', monospace; text-align: center;">
                                    <div style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-weight: 600; color: #495057;">
                                        #<?php echo esc_html($log->pk_value); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-family: 'Courier New', monospace; font-size: 11px; background: #f1f3f4; padding: 4px 6px; border-radius: 3px; color: #495057;">
                                        <?php echo esc_html($log->db_user ?: 'N/A'); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <div style="margin-top: 20px; text-align: center;">
                <?php
                $base_url = "?page=db-safetrigger&tab=logs";
                if ($user_filter) $base_url .= "&user_id=$user_filter";
                if ($action_filter) $base_url .= "&action_filter=$action_filter";
                if ($table_filter) $base_url .= "&table_filter=$table_filter";
                
                echo paginate_links(array(
                    'base' => $base_url . '&paged=%#%',
                    'format' => '',
                    'current' => $page,
                    'total' => $total_pages,
                    'prev_text' => '« Anterior',
                    'next_text' => 'Siguiente »',
                    'type' => 'plain'
                ));
                ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Enviar reporte de auditoría por email
 */
function dbst_send_audit_report($is_test = false) {
    global $wpdb;
    
    $api_key = get_option('dbst_mailjet_api_key');
    $secret_key = get_option('dbst_mailjet_secret_key');
    $from_email = get_option('dbst_mailjet_from_email');
    $from_name = get_option('dbst_mailjet_from_name');
    $recipients = get_option('dbst_report_recipients');
    
    if (empty($api_key) || empty($secret_key)) {
        return array('success' => false, 'message' => 'Mailjet no configurado');
    }
    
    // Obtener estadísticas
    $audit_table = dbst_get_audit_table_name();
    $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table`");
    $logs_today = $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table` WHERE DATE(event_time) = CURDATE()");
    $logs_week = $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table` WHERE event_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    
    // Obtener logs recientes
    $recent_logs = $wpdb->get_results("
        SELECT event_time, table_name, action, pk_value 
        FROM `$audit_table` 
        ORDER BY id DESC 
        LIMIT 10
    ");
    
    // Construir HTML del email
    $html_content = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
            .header { text-align: center; border-bottom: 2px solid #2271b1; padding-bottom: 20px; margin-bottom: 30px; }
            .stats { display: flex; justify-content: space-around; margin: 20px 0; }
            .stat { text-align: center; }
            .stat-number { font-size: 24px; font-weight: bold; color: #2271b1; }
            .stat-label { font-size: 12px; color: #666; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #f8f9fa; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🛡️ DB-SafeTrigger</h1>
                <h2>Reporte de Auditoría" . ($is_test ? " (PRUEBA)" : "") . "</h2>
                <p>Fecha: " . date('d/m/Y H:i:s') . "</p>
            </div>
            
            <div class='stats'>
                <div class='stat'>
                    <div class='stat-number'>$total_logs</div>
                    <div class='stat-label'>Total Logs</div>
                </div>
                <div class='stat'>
                    <div class='stat-number'>$logs_today</div>
                    <div class='stat-label'>Hoy</div>
                </div>
                <div class='stat'>
                    <div class='stat-number'>$logs_week</div>
                    <div class='stat-label'>Última Semana</div>
                </div>
            </div>
            
            <h3>📋 Actividad Reciente</h3>
            <table>
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Tabla</th>
                        <th>Acción</th>
                        <th>Registro ID</th>
                    </tr>
                </thead>
                <tbody>";
    
    foreach ($recent_logs as $log) {
        $html_content .= "
                    <tr>
                        <td>" . date('d/m H:i', strtotime($log->event_time)) . "</td>
                        <td>$log->table_name</td>
                        <td>$log->action</td>
                        <td>$log->pk_value</td>
                    </tr>";
    }
    
    $html_content .= "
                </tbody>
            </table>
            
            <div style='background: #f8f9fa; padding: 15px; border-radius: 6px; margin-top: 30px; text-align: center;'>
                <p><strong>Sistema DB-SafeTrigger v1.1.0</strong></p>
                <p>Sistema de Auditoría y Trazabilidad para WordPress</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Preparar destinatarios
    $recipient_emails = array_filter(array_map('trim', explode("\n", $recipients)));
    if (empty($recipient_emails)) {
        return array('success' => false, 'message' => 'No hay destinatarios configurados');
    }
    
    $to_array = array();
    foreach ($recipient_emails as $email) {
        if (is_email($email)) {
            $to_array[] = array('Email' => $email);
        }
    }
    
    // Preparar datos para Mailjet
    $data = array(
        'Messages' => array(
            array(
                'From' => array(
                    'Email' => $from_email,
                    'Name' => $from_name
                ),
                'To' => $to_array,
                'Subject' => 'Reporte de Auditoría DB-SafeTrigger - ' . date('d/m/Y'),
                'HTMLPart' => $html_content
            )
        )
    );
    
    // Enviar email
    $auth = base64_encode($api_key . ':' . $secret_key);
    
    $response = wp_remote_post('https://api.mailjet.com/v3.1/send', array(
        'headers' => array(
            'Authorization' => 'Basic ' . $auth,
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode($data),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return array('success' => false, 'message' => $response->get_error_message());
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    
    if ($response_code === 200) {
        return array('success' => true, 'message' => 'Email enviado correctamente');
    } else {
        $body = wp_remote_retrieve_body($response);
        return array('success' => false, 'message' => 'Error en envío: ' . $response_code . ' - ' . $body);
    }
}

/**
 * Función para reporte diario automatizado
 */
function dbst_send_daily_report() {
    if (get_option('dbst_daily_report_enabled', 1)) {
        dbst_send_audit_report(false);
    }
}

/**
 * Cargar clases auxiliares
 */
function dbst_load_classes() {
    $classes = array(
        'DBST_Session' => 'inc/class-dbst-session.php',
        'DBST_Upgrader' => 'inc/class-dbst-upgrader.php',
        'DBST_Installer' => 'inc/class-dbst-installer.php',
        'DB_SafeTrigger_i18n' => 'includes/class-db-safetrigger-i18n.php'
    );
    
    foreach ($classes as $class_name => $file_path) {
        $full_path = plugin_dir_path(__FILE__) . $file_path;
        if (file_exists($full_path) && !class_exists($class_name)) {
            require_once $full_path;
        }
    }
    
    // Inicializar internacionalización si la clase existe
    if (class_exists('DB_SafeTrigger_i18n')) {
        DB_SafeTrigger_i18n::init();
    }
}

// Cargar clases al inicio
add_action('init', 'dbst_load_classes');

// Cargar internacionalización en hooks adicionales
add_action('plugins_loaded', 'dbst_load_textdomain', 1);
add_action('init', 'dbst_load_textdomain', 5);
add_action('admin_init', 'dbst_load_textdomain', 1);

// Inicializar sesión de usuario si la clase existe
add_action('init', function() {
    if (class_exists('DBST_Session')) {
        DBST_Session::init();
    }
});

// Hook para capturar usuario en operaciones de WordPress
add_action('wp_loaded', 'dbst_set_user_for_triggers');
add_action('admin_init', 'dbst_set_user_for_triggers');

/**
 * Establecer usuario actual para triggers en cada request
 */
function dbst_set_user_for_triggers() {
    global $wpdb;
    
    $current_user_id = get_current_user_id();
    if ($current_user_id) {
        $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $current_user_id));
        $wpdb->query($wpdb->prepare("SET @wp_user = %d", $current_user_id));
        
        // También establecer en hooks específicos de WordPress
        add_action('pre_post_update', function($post_id) use ($wpdb, $current_user_id) {
            $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $current_user_id));
        });
        
        add_action('wp_delete_post', function($post_id) use ($wpdb, $current_user_id) {
            $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $current_user_id));
        });
        
        add_action('profile_update', function($user_id) use ($wpdb, $current_user_id) {
            $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $current_user_id));
        });
        
        add_action('wp_delete_user', function($user_id) use ($wpdb, $current_user_id) {
            $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $current_user_id));
        });
        
        add_action('wp_update_comment', function($comment_id) use ($wpdb, $current_user_id) {
            $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $current_user_id));
        });
        
        add_action('wp_delete_comment', function($comment_id) use ($wpdb, $current_user_id) {
            $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $current_user_id));
        });
    }
}

/**
 * Función principal para crear triggers (versión definitiva)
 */
function dbst_create_triggers_definitivo() {
    global $wpdb;
    
    // Detectar prefijo de base de datos
    $prefix = $wpdb->prefix;
    $audit_table = dbst_get_audit_table_name();
    

    $results[] = "�️ <strong>Configurando Sistema de Auditoría</strong>";

    
    // Verificar que la tabla de auditoría existe
    $audit_table = dbst_get_audit_table_name();
    $audit_table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $audit_table)) === $audit_table;
    if (!$audit_table_exists) {
        return array('type' => 'error', 'message' => '❌ Error: No se pudo encontrar la tabla de auditoría. Por favor, reactiva el plugin.');
    }
    
    // Verificar campo wp_user_id en tabla de auditoría
    $columns = $wpdb->get_results("DESCRIBE `$audit_table`");
    $has_wp_user_id = false;
    foreach ($columns as $column) {
        if ($column->Field === 'wp_user_id') {
            $has_wp_user_id = true;
            break;
        }
    }
    
    if (!$has_wp_user_id) {

        $add_column = $wpdb->query("ALTER TABLE `$audit_table` ADD COLUMN wp_user_id BIGINT UNSIGNED NULL AFTER db_user");
        
        if ($add_column !== false) {

            $wpdb->query("ALTER TABLE `$audit_table` ADD INDEX idx_wp_user_time (wp_user_id, event_time)");
        } else {
            return array('type' => 'error', 'message' => '❌ Error al optimizar la estructura de la base de datos.');
        }
    } else {

    }
    
    // Configurar captura de usuario si la clase existe
    if (class_exists('DBST_Session')) {
        DBST_Session::force_set_user();

    }
    
    // Definir tablas a monitorear
    $tables_to_monitor = array(
        'posts' => 'ID',
        'users' => 'ID', 
        'comments' => 'comment_ID'
    );
    
    $created_count = 0;
    $error_count = 0;
    
    foreach ($tables_to_monitor as $table_suffix => $primary_key) {
        $full_table_name = $prefix . $table_suffix;
        
        // Verificar que la tabla existe
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $full_table_name)) === $full_table_name;
        if (!$table_exists) {

            continue;
        }
        
        $results[] = "� <strong>Configurando monitoreo para: $table_suffix</strong>";
        
        // === CREAR TRIGGER UPDATE ===
        $trigger_name_update = "trg_{$table_suffix}_au"; // After Update
        
        // Eliminar trigger existente
        $wpdb->query("DROP TRIGGER IF EXISTS `$trigger_name_update`");
        
        $trigger_sql_update = "
        CREATE TRIGGER `$trigger_name_update`
        AFTER UPDATE ON `$full_table_name`
        FOR EACH ROW
        BEGIN
            DECLARE wp_user_captured BIGINT DEFAULT NULL;
            
            -- Capturar usuario de múltiples fuentes
            SET wp_user_captured = COALESCE(@wp_current_user_id, @wp_user, NULL);
            
            -- Si no se capturó usuario, intentar desde CONNECTION_ID()
            IF wp_user_captured IS NULL THEN
                SET wp_user_captured = @wp_current_user_id;
            END IF;
            
            INSERT INTO `$audit_table` (
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
                COALESCE(USER(), 'unknown'), 
                wp_user_captured, 
                '$full_table_name', 
                'UPDATE', 
                OLD.$primary_key,
                CONCAT('{\"id\":', COALESCE(OLD.$primary_key, 0), '}'), 
                COALESCE(SUBSTRING_INDEX(USER(),'@',-1), 'localhost')
            );
        END";
        
        $result_update = $wpdb->query($trigger_sql_update);
        if ($result_update !== false) {

            $created_count++;
        } else {

            $error_count++;
        }
        
        // === CREAR TRIGGER DELETE ===
        $trigger_name_delete = "trg_{$table_suffix}_bd"; // Before Delete
        
        // Eliminar trigger existente
        $wpdb->query("DROP TRIGGER IF EXISTS `$trigger_name_delete`");
        
        $trigger_sql_delete = "
        CREATE TRIGGER `$trigger_name_delete`
        BEFORE DELETE ON `$full_table_name`
        FOR EACH ROW
        BEGIN
            DECLARE wp_user_captured BIGINT DEFAULT NULL;
            
            -- Capturar usuario de múltiples fuentes
            SET wp_user_captured = COALESCE(@wp_current_user_id, @wp_user, NULL);
            
            -- Si no se capturó usuario, intentar desde CONNECTION_ID()
            IF wp_user_captured IS NULL THEN
                SET wp_user_captured = @wp_current_user_id;
            END IF;
            
            INSERT INTO `$audit_table` (
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
                COALESCE(USER(), 'unknown'), 
                wp_user_captured, 
                '$full_table_name', 
                'DELETE', 
                OLD.$primary_key,
                CONCAT('{\"id\":', COALESCE(OLD.$primary_key, 0), '}'), 
                COALESCE(SUBSTRING_INDEX(USER(),'@',-1), 'localhost')
            );
        END";
        
        $result_delete = $wpdb->query($trigger_sql_delete);
        if ($result_delete !== false) {

            $created_count++;
        } else {

            $error_count++;
        }
    }
    



    
    // Verificar triggers creados
    $active_triggers = $wpdb->get_results("SHOW TRIGGERS");
    $db_triggers = array_filter($active_triggers, function($t) { return strpos($t->Trigger, 'trg_') === 0; });
    $results[] = "� Sistemas de monitoreo activos: <strong>" . count($db_triggers) . "</strong>";
    

    
    $message = __('✅ Sistema de auditoría configurado exitosamente. El monitoreo está activo.', 'db-safetrigger');
    $type = 'success';
    
    if ($error_count > 0) {
        $message = '❌ Se produjeron algunos errores durante la configuración. Por favor, inténtalo nuevamente.';
        $type = 'error';
    }
    
    if ($created_count === 0) {
        $message = '❌ No se pudieron crear los triggers. Verifica los permisos de la base de datos.';
        $type = 'error';
    }
    
    return array('type' => $type, 'message' => $message);
}

/**
 * Función para migrar datos de la tabla antigua log_auditoria a la nueva con prefijo
 */
function dbst_migrate_old_table() {
    global $wpdb;
    
    $old_table = 'log_auditoria';
    $new_table = dbst_get_audit_table_name();
    
    // Verificar si la tabla antigua existe
    $old_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$old_table'") === $old_table;
    if (!$old_table_exists) {
        return; // No hay nada que migrar
    }
    
    // Verificar si la tabla nueva existe
    $new_table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $new_table)) === $new_table;
    if (!$new_table_exists) {
        return; // La tabla nueva no existe, no se puede migrar
    }
    
    // Verificar si ya se migró (evitar duplicaciones)
    $migration_done = get_option('dbst_migration_completed', false);
    if ($migration_done) {
        return;
    }
    
    // Contar registros en tabla antigua
    $old_count = $wpdb->get_var("SELECT COUNT(*) FROM `$old_table`");
    if ($old_count == 0) {
        // No hay datos que migrar, marcar como completado
        update_option('dbst_migration_completed', true);
        return;
    }
    
    // Migrar datos
    $migration_query = "
        INSERT INTO `$new_table` (
            id, event_time, db_user, wp_user_id, table_name, 
            action, pk_value, old_data, client_host
        )
        SELECT 
            id, event_time, db_user, wp_user_id, table_name, 
            action, pk_value, old_data, client_host
        FROM `$old_table`
        WHERE NOT EXISTS (
            SELECT 1 FROM `$new_table` 
            WHERE `$new_table`.id = `$old_table`.id
        )
    ";
    
    $result = $wpdb->query($migration_query);
    
    if ($result !== false) {
        // Migración exitosa
        update_option('dbst_migration_completed', true);
        update_option('dbst_migration_records', $old_count);
        update_option('dbst_migration_date', current_time('mysql'));
        
        // Opcional: eliminar tabla antigua después de la migración
        // $wpdb->query("DROP TABLE IF EXISTS `$old_table`");
        // add_option('dbst_old_table_dropped', true);
    } else {
        // Error en la migración
        update_option('dbst_migration_error', $wpdb->last_error);
    }
}

/**
 * Eliminar todas las opciones del plugin y devolver conteo
 */
function dbst_remove_all_plugin_options() {
    $options = array(
        // Opciones principales
        'dbst_json_support', 'dbst_last_sql_error', 'db_safetrigger_version',
        'dbst_daily_report_enabled', 'dbst_admin_email', 'db_safetrigger_enabled',
        'db_safetrigger_monitor_tables', 'dbst_create_triggers_needed', 'dbst_monitor_tables',
        
        // Opciones de Mailjet
        'dbst_mailjet_api_key', 'dbst_mailjet_secret_key', 'dbst_mailjet_from_email',
        'dbst_mailjet_from_name', 'dbst_mailjet_sandbox_mode', 'dbst_report_recipients',
        'dbst_last_mailjet_error', 'dbst_last_message_id', 'dbst_last_message_uuid',
        'dbst_last_report_sent', 'dbst_last_report_success', 'dbst_last_email_method',
        
        // Opciones de migración
        'dbst_migration_completed', 'dbst_migration_records', 'dbst_migration_date',
        'dbst_migration_error', 'dbst_old_table_dropped',
        
        // Opciones de actualización
        'dbst_upgrade_1_1_0_completed', 'dbst_triggers_need_update'
    );
    
    $deleted_count = 0;
    foreach ($options as $option) {
        if (delete_option($option)) {
            $deleted_count++;
        }
    }
    
    return $deleted_count;
}

/**
 * Enviar reporte de auditoría instantáneo
 */
function dbst_send_instant_audit_report() {
    global $wpdb;
    
    try {
        $audit_table = dbst_get_audit_table_name();
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $audit_table)) === $audit_table;
        
        if (!$table_exists) {
            return array(
                'success' => false,
                'message' => '❌ No se puede enviar el reporte: la tabla de auditoría no existe.'
            );
        }
        
        // Obtener estadísticas de hoy
        $today = date('Y-m-d');
        $stats = array(
            'total' => $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table` WHERE DATE(event_time) = '$today'"),
            'updates' => $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table` WHERE action = 'UPDATE' AND DATE(event_time) = '$today'"),
            'deletes' => $wpdb->get_var("SELECT COUNT(*) FROM `$audit_table` WHERE action = 'DELETE' AND DATE(event_time) = '$today'"),
            'tables' => $wpdb->get_results("SELECT table_name, COUNT(*) as count FROM `$audit_table` WHERE DATE(event_time) = '$today' GROUP BY table_name ORDER BY count DESC")
        );
        
        // Obtener eventos recientes (últimos 20)
        $recent_events = $wpdb->get_results("
            SELECT l.*, u.display_name, u.user_login
            FROM `$audit_table` l
            LEFT JOIN {$wpdb->users} u ON l.wp_user_id = u.ID
            WHERE DATE(l.event_time) = '$today'
            ORDER BY l.id DESC 
            LIMIT 20
        ");
        
        // Generar contenido del reporte
        $report_content = dbst_generate_report_content($stats, $recent_events, 'instant');
        
        // Obtener destinatarios
        $recipients = get_option('dbst_report_recipients', get_option('admin_email'));
        $recipient_list = array_filter(array_map('trim', explode(',', $recipients)));
        
        if (empty($recipient_list)) {
            return array(
                'success' => false,
                'message' => '❌ No hay destinatarios configurados para el reporte.'
            );
        }
        
        // Enviar reporte
        $mailjet_result = dbst_send_mailjet_report($recipient_list, $report_content, 'instant');
        
        if ($mailjet_result['success']) {
            update_option('dbst_last_report_sent', current_time('mysql'));
            return array(
                'success' => true,
                'message' => "✅ Reporte enviado exitosamente a " . count($recipient_list) . " destinatario(s)."
            );
        } else {
            return array(
                'success' => false,
                'message' => '❌ Error enviando reporte: ' . $mailjet_result['message']
            );
        }
        
    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => '❌ Error generando reporte: ' . $e->getMessage()
        );
    }
}

/**
 * Generar contenido HTML del reporte
 */
function dbst_generate_report_content($stats, $recent_events, $type = 'daily') {
    $site_name = get_bloginfo('name');
    $site_url = get_site_url();
    $date = date('Y-m-d H:i:s');
    $report_title = $type === 'instant' ? 'Reporte Instantáneo' : 'Reporte Diario';
    
    $content = "
    <div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; background: #f9f9f9; padding: 20px;'>
        <div style='background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
            <header style='text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2271b1; padding-bottom: 20px;'>
                <h1 style='color: #2271b1; margin: 0;'>🛡️ DB-SafeTrigger</h1>
                <h2 style='color: #666; margin: 10px 0 0 0;'>📊 $report_title de Auditoría</h2>
                <p style='color: #888; margin: 5px 0 0 0;'>$site_name • $date</p>
            </header>
            
            <section style='margin-bottom: 30px;'>
                <h3 style='color: #2271b1; border-left: 4px solid #2271b1; padding-left: 15px;'>📈 Resumen de Actividad - " . date('d/m/Y') . "</h3>
                <div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0;'>
                    <div style='background: #e7f3ff; padding: 20px; border-radius: 6px; text-align: center; border: 1px solid #b3d9ff;'>
                        <div style='font-size: 32px; font-weight: bold; color: #0073aa;'>" . number_format($stats['total']) . "</div>
                        <div style='color: #666; font-weight: 600;'>Total Eventos</div>
                    </div>
                    <div style='background: #e6f7ff; padding: 20px; border-radius: 6px; text-align: center; border: 1px solid #91d5ff;'>
                        <div style='font-size: 32px; font-weight: bold; color: #1890ff;'>" . number_format($stats['updates']) . "</div>
                        <div style='color: #666; font-weight: 600;'>Actualizaciones</div>
                    </div>
                    <div style='background: #fff2e6; padding: 20px; border-radius: 6px; text-align: center; border: 1px solid #ffcc99;'>
                        <div style='font-size: 32px; font-weight: bold; color: #fa8c16;'>" . number_format($stats['deletes']) . "</div>
                        <div style='color: #666; font-weight: 600;'>Eliminaciones</div>
                    </div>
                </div>
            </section>";
    
    // Actividad por tabla
    if (!empty($stats['tables'])) {
        $content .= "
            <section style='margin-bottom: 30px;'>
                <h3 style='color: #2271b1; border-left: 4px solid #2271b1; padding-left: 15px;'>📋 Actividad por Tabla</h3>
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <thead>
                        <tr style='background: #f8f9fa;'>
                            <th style='padding: 12px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;'>Tabla</th>
                            <th style='padding: 12px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;'>Eventos</th>
                        </tr>
                    </thead>
                    <tbody>";
        
        foreach ($stats['tables'] as $table_stat) {
            $content .= "
                        <tr>
                            <td style='padding: 10px 12px; border: 1px solid #dee2e6;'><code style='background: #f8f9fa; padding: 2px 6px; border-radius: 3px;'>" . esc_html($table_stat->table_name) . "</code></td>
                            <td style='padding: 10px 12px; border: 1px solid #dee2e6; text-align: center; font-weight: 600; color: #2271b1;'>" . number_format($table_stat->count) . "</td>
                        </tr>";
        }
        
        $content .= "
                    </tbody>
                </table>
            </section>";
    }
    
    // Eventos recientes
    if (!empty($recent_events) && get_option('dbst_report_include_details', 1)) {
        $content .= "
            <section style='margin-bottom: 30px;'>
                <h3 style='color: #2271b1; border-left: 4px solid #2271b1; padding-left: 15px;'>🔍 Eventos Recientes</h3>
                <div style='max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 6px;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <thead style='position: sticky; top: 0; background: #f8f9fa;'>
                            <tr>
                                <th style='padding: 12px 8px; text-align: left; border-bottom: 2px solid #dee2e6; font-size: 13px;'>Hora</th>
                                <th style='padding: 12px 8px; text-align: left; border-bottom: 2px solid #dee2e6; font-size: 13px;'>Usuario</th>
                                <th style='padding: 12px 8px; text-align: left; border-bottom: 2px solid #dee2e6; font-size: 13px;'>Tabla</th>
                                <th style='padding: 12px 8px; text-align: left; border-bottom: 2px solid #dee2e6; font-size: 13px;'>Acción</th>
                            </tr>
                        </thead>
                        <tbody>";
        
        foreach ($recent_events as $event) {
            $event_time = date('H:i:s', strtotime($event->event_time));
            $user_display = $event->display_name ?: $event->user_login ?: 'Sistema';
            $action_color = $event->action === 'DELETE' ? '#dc3545' : '#28a745';
            $action_icon = $event->action === 'DELETE' ? '🗑️' : '✏️';
            
            $content .= "
                            <tr style='border-bottom: 1px solid #f1f1f1;'>
                                <td style='padding: 8px; font-family: monospace; font-size: 13px; color: #666;'>$event_time</td>
                                <td style='padding: 8px; font-size: 13px;'>$user_display</td>
                                <td style='padding: 8px; font-size: 13px;'><code style='background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-size: 12px;'>" . esc_html($event->table_name) . "</code></td>
                                <td style='padding: 8px; font-size: 13px;'><span style='color: $action_color; font-weight: 600;'>$action_icon " . esc_html($event->action) . "</span></td>
                            </tr>";
        }
        
        $content .= "
                        </tbody>
                    </table>
                </div>
            </section>";
    }
    
    $content .= "
            <footer style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #888;'>
                <p style='margin: 0; font-size: 14px;'>
                    🛡️ Generado automáticamente por <strong>DB-SafeTrigger</strong><br>
                    <a href='$site_url' style='color: #2271b1; text-decoration: none;'>$site_name</a>
                </p>
            </footer>
        </div>
    </div>";
    
    return $content;
}

/**
 * Enviar reporte via Mailjet
 */
function dbst_send_mailjet_report($recipients, $content, $type = 'daily') {
    $api_key = get_option('dbst_mailjet_api_key');
    $secret_key = get_option('dbst_mailjet_secret_key');
    $from_email = get_option('dbst_mailjet_from_email');
    $from_name = get_option('dbst_mailjet_from_name', 'DB-SafeTrigger');
    
    if (empty($api_key) || empty($secret_key) || empty($from_email)) {
        return array(
            'success' => false,
            'message' => 'Configuración de Mailjet incompleta'
        );
    }
    
    $subject = $type === 'instant' ? 
        '📊 Reporte Instantáneo de Auditoría - ' . get_bloginfo('name') : 
        '📅 Reporte Diario de Auditoría - ' . get_bloginfo('name');
    
    $mailjet_data = array(
        'Messages' => array(
            array(
                'From' => array(
                    'Email' => $from_email,
                    'Name' => $from_name
                ),
                'To' => array(),
                'Subject' => $subject,
                'HTMLPart' => $content
            )
        )
    );
    
    // Añadir destinatarios
    foreach ($recipients as $email) {
        if (is_email($email)) {
            $mailjet_data['Messages'][0]['To'][] = array('Email' => $email);
        }
    }
    
    if (empty($mailjet_data['Messages'][0]['To'])) {
        return array(
            'success' => false,
            'message' => 'No hay destinatarios válidos'
        );
    }
    
    // Enviar via Mailjet
    $response = wp_remote_post('https://api.mailjet.com/v3.1/send', array(
        'timeout' => 30,
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $secret_key),
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode($mailjet_data)
    ));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => $response->get_error_message()
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($response_code === 200 && isset($response_body['Messages'][0]['Status']) && $response_body['Messages'][0]['Status'] === 'success') {
        return array(
            'success' => true,
            'message' => 'Reporte enviado exitosamente'
        );
    } else {
        $error_message = isset($response_body['ErrorMessage']) ? $response_body['ErrorMessage'] : 'Error desconocido';
        return array(
            'success' => false,
            'message' => $error_message
        );
    }
}
?>