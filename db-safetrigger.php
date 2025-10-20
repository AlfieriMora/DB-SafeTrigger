<?php
/**
 * Plugin Name: DB-SafeTrigger
 * Description: Plugin de Trazabilidad y Auditoría a Nivel de Base de Datos para WordPress con Mailjet v3.1
 * Version: 1.0.4-final
 * Author: Alfieri Mora
 * Text Domain: db-safetrigger
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('DB_SAFETRIGGER_VERSION', '1.0.4-final');
define('DB_SAFETRIGGER_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Hooks de activación
register_activation_hook(__FILE__, 'dbst_activate_plugin');
register_deactivation_hook(__FILE__, 'dbst_deactivate_plugin');

/**
 * Activación del plugin
 */
function dbst_activate_plugin() {
    global $wpdb;
    
    // Crear tabla de auditoría
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = 'log_auditoria';
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        db_user VARCHAR(128),
        table_name VARCHAR(128) NOT NULL,
        action ENUM('UPDATE','DELETE') NOT NULL,
        pk_value VARCHAR(64) NOT NULL,
        old_data LONGTEXT,
        client_host VARCHAR(255),
        PRIMARY KEY (id),
        KEY idx_table_time (table_name, event_time),
        KEY idx_action_time (action, event_time)
    ) ENGINE=InnoDB $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Configuraciones por defecto
    add_option('dbst_activated', current_time('mysql'));
    add_option('dbst_version', DB_SAFETRIGGER_VERSION);
    add_option('dbst_mailjet_api_key', '');
    add_option('dbst_mailjet_secret_key', '');
    add_option('dbst_mailjet_from_email', get_option('admin_email'));
    add_option('dbst_mailjet_from_name', get_bloginfo('name'));
    add_option('dbst_report_recipients', get_option('admin_email'));
    add_option('dbst_daily_reports', 1);
    add_option('dbst_report_time', '09:00');
    
    // Programar reporte diario
    if (!wp_next_scheduled('dbst_daily_audit_report')) {
        wp_schedule_event(time(), 'daily', 'dbst_daily_audit_report');
    }
}

/**
 * Desactivación del plugin
 */
function dbst_deactivate_plugin() {
    wp_clear_scheduled_hook('dbst_daily_audit_report');
}

// Agregar menú de administración
add_action('admin_menu', function() {
    add_options_page(
        'DB-SafeTrigger',
        'DB-SafeTrigger',
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
    global $wpdb;
    
    $message = '';
    $message_type = '';
    
    // Procesar acciones
    if (isset($_GET['action']) && wp_verify_nonce($_GET['_wpnonce'], 'dbst_action')) {
        switch ($_GET['action']) {
            case 'create_triggers':
                $result = dbst_create_triggers();
                $message = $result['message'];
                $message_type = $result['type'];
                break;
                
            case 'test_mailjet':
                $result = dbst_test_mailjet();
                $message = $result['message'];
                $message_type = $result['type'];
                break;
                
            case 'send_test_report':
                $result = dbst_send_test_report();
                $message = $result['message'];
                $message_type = $result['type'];
                break;
        }
    }
    
    // Procesar configuración
    if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['dbst_nonce'], 'dbst_settings')) {
        update_option('dbst_mailjet_api_key', sanitize_text_field($_POST['mailjet_api_key']));
        update_option('dbst_mailjet_secret_key', sanitize_text_field($_POST['mailjet_secret_key']));
        update_option('dbst_mailjet_from_email', sanitize_email($_POST['mailjet_from_email']));
        update_option('dbst_mailjet_from_name', sanitize_text_field($_POST['mailjet_from_name']));
        update_option('dbst_report_recipients', sanitize_textarea_field($_POST['report_recipients']));
        update_option('dbst_daily_reports', isset($_POST['daily_reports']) ? 1 : 0);
        update_option('dbst_report_time', sanitize_text_field($_POST['report_time']));
        
        $message = '✅ Configuración guardada correctamente.';
        $message_type = 'success';
    }
    
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'status';
    $nonce = wp_create_nonce('dbst_action');
    
    ?>
    <div class="wrap">
        <h1>🔒 DB-SafeTrigger</h1>
        <p>Plugin de Trazabilidad y Auditoría a Nivel de Base de Datos para WordPress</p>
        
        <?php if ($message): ?>
        <div class="notice notice-<?php echo $message_type; ?>">
            <p><?php echo wp_kses_post($message); ?></p>
        </div>
        <?php endif; ?>
        
        <nav class="nav-tab-wrapper">
            <a href="?page=db-safetrigger&tab=status" class="nav-tab <?php echo $active_tab == 'status' ? 'nav-tab-active' : ''; ?>">
                📊 Estado del Sistema
            </a>
            <a href="?page=db-safetrigger&tab=triggers" class="nav-tab <?php echo $active_tab == 'triggers' ? 'nav-tab-active' : ''; ?>">
                🔧 Gestión de Triggers
            </a>
            <a href="?page=db-safetrigger&tab=mailjet" class="nav-tab <?php echo $active_tab == 'mailjet' ? 'nav-tab-active' : ''; ?>">
                📧 Configuración Mailjet
            </a>
            <a href="?page=db-safetrigger&tab=reports" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : ''; ?>">
                📋 Reportes
            </a>
            <a href="?page=db-safetrigger&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">
                📜 Logs de Auditoría
            </a>
        </nav>
        
        <div class="tab-content">
            <?php
            switch($active_tab) {
                case 'triggers':
                    dbst_triggers_tab($nonce);
                    break;
                case 'mailjet':
                    dbst_mailjet_tab();
                    break;
                case 'reports':
                    dbst_reports_tab($nonce);
                    break;
                case 'logs':
                    dbst_logs_tab();
                    break;
                default:
                    dbst_status_tab();
                    break;
            }
            ?>
        </div>
    </div>
    
    <style>
    .dbst-card {
        background: white;
        border: 1px solid #c3c4c7;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    .dbst-status-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f1;
    }
    .dbst-status-ok { color: #2271b1; font-weight: 600; }
    .dbst-status-error { color: #d63638; font-weight: 600; }
    .dbst-status-warning { color: #dba617; font-weight: 600; }
    .dbst-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) {
        .dbst-grid { grid-template-columns: 1fr; }
    }
    </style>
    <?php
}

/**
 * Tab de Estado del Sistema
 */
function dbst_status_tab() {
    global $wpdb;
    
    // Verificaciones del sistema
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE 'log_auditoria'") === 'log_auditoria';
    
    $triggers = $wpdb->get_results("SHOW TRIGGERS");
    $trigger_count = 0;
    $our_triggers = array();
    foreach($triggers as $trigger) {
        if (strpos($trigger->Trigger, 'trg_') === 0) {
            $trigger_count++;
            $our_triggers[] = $trigger->Trigger;
        }
    }
    
    $mailjet_configured = !empty(get_option('dbst_mailjet_api_key')) && !empty(get_option('dbst_mailjet_secret_key'));
    
    $log_count = $wpdb->get_var("SELECT COUNT(*) FROM log_auditoria");
    $log_count_today = $wpdb->get_var("SELECT COUNT(*) FROM log_auditoria WHERE DATE(event_time) = CURDATE()");
    
    ?>
    <div class="dbst-grid">
        <div class="dbst-card">
            <h2>🔍 Estado del Sistema</h2>
            
            <div class="dbst-status-item">
                <span>Tabla log_auditoria:</span>
                <span class="<?php echo $table_exists ? 'dbst-status-ok' : 'dbst-status-error'; ?>">
                    <?php echo $table_exists ? '✅ Existe' : '❌ No existe'; ?>
                </span>
            </div>
            
            <div class="dbst-status-item">
                <span>Triggers de auditoría:</span>
                <span class="<?php echo $trigger_count > 0 ? 'dbst-status-ok' : 'dbst-status-error'; ?>">
                    <?php echo $trigger_count; ?> activos
                </span>
            </div>
            
            <div class="dbst-status-item">
                <span>Configuración Mailjet:</span>
                <span class="<?php echo $mailjet_configured ? 'dbst-status-ok' : 'dbst-status-warning'; ?>">
                    <?php echo $mailjet_configured ? '✅ Configurado' : '⚠️ Pendiente'; ?>
                </span>
            </div>
            
            <div class="dbst-status-item">
                <span>Reportes diarios:</span>
                <span class="<?php echo get_option('dbst_daily_reports') ? 'dbst-status-ok' : 'dbst-status-warning'; ?>">
                    <?php echo get_option('dbst_daily_reports') ? '✅ Activos' : '⚠️ Desactivados'; ?>
                </span>
            </div>
        </div>
        
        <div class="dbst-card">
            <h2>📊 Estadísticas</h2>
            
            <div class="dbst-status-item">
                <span>Total de logs:</span>
                <span class="dbst-status-ok"><?php echo number_format($log_count); ?></span>
            </div>
            
            <div class="dbst-status-item">
                <span>Logs hoy:</span>
                <span class="dbst-status-ok"><?php echo number_format($log_count_today); ?></span>
            </div>
            
            <div class="dbst-status-item">
                <span>Usuario BD:</span>
                <span class="dbst-status-ok"><?php echo esc_html($wpdb->get_var("SELECT USER()")); ?></span>
            </div>
            
            <div class="dbst-status-item">
                <span>Prefijo BD:</span>
                <span class="dbst-status-ok"><?php echo esc_html($wpdb->prefix); ?></span>
            </div>
            
            <div class="dbst-status-item">
                <span>Plugin activado:</span>
                <span class="dbst-status-ok"><?php echo get_option('dbst_activated'); ?></span>
            </div>
        </div>
    </div>
    
    <?php if (!empty($our_triggers)): ?>
    <div class="dbst-card">
        <h3>🔧 Triggers Activos</h3>
        <p><strong>Triggers encontrados:</strong> <?php echo implode(', ', $our_triggers); ?></p>
        <p><em>Estos triggers están monitoreando cambios en las tablas de WordPress.</em></p>
    </div>
    <?php endif; ?>
    <?php
}

/**
 * Tab de Gestión de Triggers
 */
function dbst_triggers_tab($nonce) {
    global $wpdb;
    
    $tables_info = array(
        'posts' => '📝 Entradas y páginas',
        'users' => '👥 Usuarios',
        'comments' => '💬 Comentarios'
    );
    
    ?>
    <div class="dbst-card">
        <h2>🔧 Gestión de Triggers de Auditoría</h2>
        <p>Los triggers de base de datos monitorizan automáticamente los cambios en las tablas importantes de WordPress.</p>
        
        <h3>📋 Tablas Monitoreadas</h3>
        <ul>
            <?php foreach($tables_info as $table => $description): ?>
            <li>✅ <code><?php echo $wpdb->prefix . $table; ?></code> - <?php echo $description; ?></li>
            <?php endforeach; ?>
        </ul>
        
        <h3>🎯 Acciones de Monitoreo</h3>
        <ul>
            <li><strong>UPDATE:</strong> Registra cuando se modifica un registro existente</li>
            <li><strong>DELETE:</strong> Registra cuando se elimina un registro</li>
        </ul>
        
        <p>
            <a href="<?php echo admin_url('options-general.php?page=db-safetrigger&tab=triggers&action=create_triggers&_wpnonce=' . $nonce); ?>" 
               class="button button-primary">
                🚀 Crear/Actualizar Triggers de Auditoría
            </a>
        </p>
        
        <div class="notice notice-info">
            <p><strong>ℹ️ Información:</strong> Los triggers se crean automáticamente para monitorear cambios. Si ya existen, serán actualizados.</p>
        </div>
    </div>
    <?php
}

/**
 * Tab de Configuración Mailjet
 */
function dbst_mailjet_tab() {
    $api_key = get_option('dbst_mailjet_api_key', '');
    $secret_key = get_option('dbst_mailjet_secret_key', '');
    $from_email = get_option('dbst_mailjet_from_email', get_option('admin_email'));
    $from_name = get_option('dbst_mailjet_from_name', get_bloginfo('name'));
    $recipients = get_option('dbst_report_recipients', get_option('admin_email'));
    $daily_reports = get_option('dbst_daily_reports', 1);
    $report_time = get_option('dbst_report_time', '09:00');
    
    ?>
    <form method="post">
        <?php wp_nonce_field('dbst_settings', 'dbst_nonce'); ?>
        
        <div class="dbst-grid">
            <div class="dbst-card">
                <h2>📧 Configuración de Mailjet</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">API Key</th>
                        <td>
                            <input type="text" name="mailjet_api_key" value="<?php echo esc_attr($api_key); ?>" 
                                   class="regular-text" placeholder="Tu API Key de Mailjet" />
                            <p class="description">Obten tu API Key desde el panel de Mailjet</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Secret Key</th>
                        <td>
                            <input type="password" name="mailjet_secret_key" value="<?php echo esc_attr($secret_key); ?>" 
                                   class="regular-text" placeholder="Tu Secret Key de Mailjet" />
                            <p class="description">Tu Secret Key para autenticación</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Email remitente</th>
                        <td>
                            <input type="email" name="mailjet_from_email" value="<?php echo esc_attr($from_email); ?>" 
                                   class="regular-text" required />
                            <p class="description">Email desde el cual se enviarán los reportes</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Nombre remitente</th>
                        <td>
                            <input type="text" name="mailjet_from_name" value="<?php echo esc_attr($from_name); ?>" 
                                   class="regular-text" required />
                            <p class="description">Nombre que aparecerá como remitente</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="dbst-card">
                <h2>📋 Configuración de Reportes</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Destinatarios</th>
                        <td>
                            <textarea name="report_recipients" rows="3" class="large-text"><?php echo esc_textarea($recipients); ?></textarea>
                            <p class="description">Emails separados por comas que recibirán los reportes</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Reportes diarios</th>
                        <td>
                            <label>
                                <input type="checkbox" name="daily_reports" value="1" <?php checked($daily_reports); ?> />
                                Enviar reportes diarios automáticamente
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Hora del reporte</th>
                        <td>
                            <input type="time" name="report_time" value="<?php echo esc_attr($report_time); ?>" />
                            <p class="description">Hora a la que se enviará el reporte diario</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="save_settings" class="button-primary" value="💾 Guardar Configuración" />
        </p>
    </form>
    <?php
}

/**
 * Tab de Reportes
 */
function dbst_reports_tab($nonce) {
    ?>
    <div class="dbst-card">
        <h2>📋 Gestión de Reportes</h2>
        <p>Envía reportes de auditoría por email usando Mailjet v3.1.</p>
        
        <h3>🧪 Pruebas de Email</h3>
        <p>Antes de configurar reportes automáticos, verifica que la configuración de Mailjet funciona correctamente.</p>
        
        <p>
            <a href="<?php echo admin_url('options-general.php?page=db-safetrigger&tab=reports&action=test_mailjet&_wpnonce=' . $nonce); ?>" 
               class="button button-secondary">
                🔧 Probar Conexión Mailjet
            </a>
            
            <a href="<?php echo admin_url('options-general.php?page=db-safetrigger&tab=reports&action=send_test_report&_wpnonce=' . $nonce); ?>" 
               class="button button-primary">
                📧 Enviar Reporte de Prueba
            </a>
        </p>
        
        <div class="notice notice-info">
            <p><strong>ℹ️ Reportes Automáticos:</strong> Si tienes los reportes diarios activados, se enviarán automáticamente cada día a la hora configurada con un resumen de la actividad de auditoría.</p>
        </div>
        
        <h3>📊 Contenido del Reporte</h3>
        <ul>
            <li>📈 Estadísticas de actividad del día</li>
            <li>🔍 Cambios detectados en tablas monitoreadas</li>
            <li>⚡ Resumen de triggers activos</li>
            <li>📋 Logs de auditoría recientes</li>
        </ul>
    </div>
    <?php
}

/**
 * Tab de Logs
 */
function dbst_logs_tab() {
    global $wpdb;
    
    // Parámetros de paginación
    $per_page = 20;
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($page - 1) * $per_page;
    
    // Obtener total de registros
    $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM log_auditoria");
    
    // Obtener logs paginados
    $logs = $wpdb->get_results($wpdb->prepare("
        SELECT id, event_time, table_name, action, pk_value, db_user, client_host
        FROM log_auditoria 
        ORDER BY id DESC 
        LIMIT %d OFFSET %d
    ", $per_page, $offset));
    
    // Calcular número de páginas
    $total_pages = ceil($total_logs / $per_page);
    
    ?>
    <div class="dbst-card">
        <h2>📜 Logs de Auditoría</h2>
        <p>Registro completo de cambios detectados en las tablas monitoreadas.</p>
        
        <div style="margin-bottom: 15px;">
            <strong>Total de registros:</strong> <?php echo number_format($total_logs); ?>
            <?php if ($total_pages > 1): ?>
            | <strong>Página:</strong> <?php echo $page; ?> de <?php echo $total_pages; ?>
            <?php endif; ?>
        </div>
        
        <?php if(empty($logs)): ?>
            <div class="notice notice-warning">
                <p>No hay registros de auditoría disponibles. Los logs aparecerán aquí cuando se detecten cambios en las tablas monitoreadas.</p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 140px;">Fecha/Hora</th>
                        <th>Tabla</th>
                        <th style="width: 80px;">Acción</th>
                        <th style="width: 80px;">PK</th>
                        <th>Usuario BD</th>
                        <th style="width: 120px;">Host</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($logs as $log): ?>
                    <tr>
                        <td><?php echo $log->id; ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($log->event_time)); ?></td>
                        <td><?php echo esc_html($log->table_name); ?></td>
                        <td>
                            <span style="color: <?php echo $log->action === 'DELETE' ? '#d63638' : '#2271b1'; ?>; font-weight: 600;">
                                <?php echo $log->action; ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($log->pk_value); ?></td>
                        <td><?php echo esc_html($log->db_user); ?></td>
                        <td><?php echo esc_html($log->client_host); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php
                    $page_links = paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo; Anterior',
                        'next_text' => 'Siguiente &raquo;',
                        'total' => $total_pages,
                        'current' => $page
                    ));
                    if ($page_links) {
                        echo $page_links;
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Crear triggers de auditoría
 */
function dbst_create_triggers() {
    global $wpdb;
    
    $tables = array(
        'posts' => 'ID',
        'users' => 'ID',
        'comments' => 'comment_ID'
    );
    
    $results = array();
    $created_count = 0;
    $error_count = 0;
    
    foreach($tables as $table => $pk) {
        $full_table_name = $wpdb->prefix . $table;
        
        // Verificar que la tabla existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") === $full_table_name;
        if (!$table_exists) {
            $results[] = "❌ Tabla $full_table_name no existe";
            continue;
        }
        
        // Crear trigger UPDATE
        $trigger_name_update = "trg_{$table}_bu";
        $wpdb->query("DROP TRIGGER IF EXISTS `$trigger_name_update`");
        
        $trigger_sql = "CREATE TRIGGER `$trigger_name_update`
        BEFORE UPDATE ON `$full_table_name`
        FOR EACH ROW
        BEGIN
            INSERT INTO log_auditoria (
                event_time, db_user, table_name, action, pk_value, old_data, client_host
            ) VALUES (
                NOW(), CURRENT_USER(), '$full_table_name', 'UPDATE', OLD.$pk,
                CONCAT('{\"id\":', OLD.$pk, '}'), SUBSTRING_INDEX(USER(),'@',-1)
            );
        END";
        
        $result = $wpdb->query($trigger_sql);
        if ($result !== false) {
            $results[] = "✅ Trigger UPDATE creado para $table";
            $created_count++;
        } else {
            $results[] = "❌ Error creando trigger UPDATE para $table: " . $wpdb->last_error;
            $error_count++;
        }
        
        // Crear trigger DELETE
        $trigger_name_delete = "trg_{$table}_bd";
        $wpdb->query("DROP TRIGGER IF EXISTS `$trigger_name_delete`");
        
        $trigger_sql = "CREATE TRIGGER `$trigger_name_delete`
        BEFORE DELETE ON `$full_table_name`
        FOR EACH ROW
        BEGIN
            INSERT INTO log_auditoria (
                event_time, db_user, table_name, action, pk_value, old_data, client_host
            ) VALUES (
                NOW(), CURRENT_USER(), '$full_table_name', 'DELETE', OLD.$pk,
                CONCAT('{\"id\":', OLD.$pk, '}'), SUBSTRING_INDEX(USER(),'@',-1)
            );
        END";
        
        $result = $wpdb->query($trigger_sql);
        if ($result !== false) {
            $results[] = "✅ Trigger DELETE creado para $table";
            $created_count++;
        } else {
            $results[] = "❌ Error creando trigger DELETE para $table: " . $wpdb->last_error;
            $error_count++;
        }
    }
    
    $summary = "📊 Resumen: $created_count triggers creados, $error_count errores";
    $results[] = $summary;
    
    $type = $error_count > 0 ? 'warning' : 'success';
    
    return array(
        'message' => "<strong>Creación de triggers completada:</strong><br>" . implode('<br>', $results),
        'type' => $type
    );
}

/**
 * Probar conexión Mailjet
 */
function dbst_test_mailjet() {
    $api_key = get_option('dbst_mailjet_api_key');
    $secret_key = get_option('dbst_mailjet_secret_key');
    
    if (empty($api_key) || empty($secret_key)) {
        return array(
            'message' => '❌ Error: API Key y Secret Key de Mailjet son requeridos.',
            'type' => 'error'
        );
    }
    
    // Test básico de API usando wp_remote_get
    $auth = base64_encode($api_key . ':' . $secret_key);
    
    $response = wp_remote_get('https://api.mailjet.com/v3/REST/apikey', array(
        'headers' => array(
            'Authorization' => 'Basic ' . $auth,
            'Content-Type' => 'application/json'
        ),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return array(
            'message' => '❌ Error de conexión: ' . $response->get_error_message(),
            'type' => 'error'
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    
    if ($response_code === 200) {
        return array(
            'message' => '✅ Conexión exitosa con Mailjet. Las credenciales son válidas.',
            'type' => 'success'
        );
    } else {
        return array(
            'message' => '❌ Error de autenticación: Código ' . $response_code . '. Verifica tus credenciales.',
            'type' => 'error'
        );
    }
}

/**
 * Enviar reporte de prueba
 */
function dbst_send_test_report() {
    $result = dbst_send_audit_report(true);
    
    if ($result['success']) {
        return array(
            'message' => '✅ Reporte de prueba enviado exitosamente. Verifica tu bandeja de entrada.',
            'type' => 'success'
        );
    } else {
        return array(
            'message' => '❌ Error enviando reporte: ' . $result['message'],
            'type' => 'error'
        );
    }
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
    $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM log_auditoria");
    $logs_today = $wpdb->get_var("SELECT COUNT(*) FROM log_auditoria WHERE DATE(event_time) = CURDATE()");
    $logs_week = $wpdb->get_var("SELECT COUNT(*) FROM log_auditoria WHERE event_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    
    // Obtener logs recientes
    $recent_logs = $wpdb->get_results("
        SELECT event_time, table_name, action, pk_value 
        FROM log_auditoria 
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
            .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔒 DB-SafeTrigger</h1>
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
                    <div class='stat-label'>Esta Semana</div>
                </div>
            </div>
            
            <h3>📋 Actividad Reciente</h3>";
    
    if (!empty($recent_logs)) {
        $html_content .= "<table>
            <tr>
                <th>Fecha/Hora</th>
                <th>Tabla</th>
                <th>Acción</th>
                <th>ID</th>
            </tr>";
        
        foreach ($recent_logs as $log) {
            $html_content .= "<tr>
                <td>" . date('d/m/Y H:i', strtotime($log->event_time)) . "</td>
                <td>" . esc_html($log->table_name) . "</td>
                <td style='color: " . ($log->action === 'DELETE' ? '#d63638' : '#2271b1') . "'>" . $log->action . "</td>
                <td>" . esc_html($log->pk_value) . "</td>
            </tr>";
        }
        
        $html_content .= "</table>";
    } else {
        $html_content .= "<p>No se detectó actividad reciente.</p>";
    }
    
    $html_content .= "
            <div class='footer'>
                <p>Este reporte fue generado automáticamente por DB-SafeTrigger</p>
                <p>Sitio web: " . get_bloginfo('name') . " (" . get_site_url() . ")</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Preparar destinatarios
    $recipients_array = array_map('trim', explode(',', $recipients));
    $to = array();
    foreach ($recipients_array as $email) {
        if (is_email($email)) {
            $to[] = array('Email' => $email);
        }
    }
    
    if (empty($to)) {
        return array('success' => false, 'message' => 'No hay destinatarios válidos');
    }
    
    // Preparar datos para Mailjet Send API v3.1
    $data = array(
        'Messages' => array(
            array(
                'From' => array(
                    'Email' => $from_email,
                    'Name' => $from_name
                ),
                'To' => $to,
                'Subject' => 'DB-SafeTrigger: Reporte de Auditoría' . ($is_test ? ' (PRUEBA)' : '') . ' - ' . date('d/m/Y'),
                'HTMLPart' => $html_content,
                'CustomID' => 'db-safetrigger-report-' . time(),
                'CustomCampaign' => 'audit-report',
                'URLTags' => 'source=db-safetrigger&type=report'
            )
        )
    );
    
    // Enviar usando Mailjet Send API v3.1
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
    $response_body = wp_remote_retrieve_body($response);
    
    if ($response_code === 200) {
        return array('success' => true, 'message' => 'Email enviado correctamente');
    } else {
        return array('success' => false, 'message' => 'Error HTTP ' . $response_code . ': ' . $response_body);
    }
}

/**
 * Enviar reporte diario (hook del cron)
 */
function dbst_send_daily_report() {
    if (get_option('dbst_daily_reports')) {
        dbst_send_audit_report();
    }
}