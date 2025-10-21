<?php
/**
 * Plugin Name: DB-SafeTrigger
 * Description: Plugin de Trazabilidad y Auditoría a Nivel de Base de Datos para WordPress con Mailjet v3.1
 * Version: 1.1.0
 * Author: Alfieri Mora
 * Text Domain: db-safetrigger
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('DB_SAFETRIGGER_VERSION', '1.1.0');
define('DB_SAFETRIGGER_PLUGIN_DIR', plugin_dir_path(__FILE__));

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
    $table_name = 'log_auditoria';
    
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
                
                $message = "✅ Se eliminaron $deleted_count triggers correctamente.";
                $message_type = 'success';
                break;
                
            case 'save_mailjet':
                update_option('dbst_mailjet_api_key', sanitize_text_field($_POST['api_key']));
                update_option('dbst_mailjet_secret_key', sanitize_text_field($_POST['secret_key']));
                update_option('dbst_mailjet_from_email', sanitize_email($_POST['from_email']));
                update_option('dbst_mailjet_from_name', sanitize_text_field($_POST['from_name']));
                update_option('dbst_report_recipients', sanitize_textarea_field($_POST['recipients']));
                
                $message = '✅ Configuración de Mailjet guardada correctamente.';
                $message_type = 'success';
                break;
                
            case 'toggle_daily_report':
                $current = get_option('dbst_daily_report_enabled', 1);
                $new_value = $current ? 0 : 1;
                update_option('dbst_daily_report_enabled', $new_value);
                
                if ($new_value) {
                    wp_schedule_event(time() + DAY_IN_SECONDS, 'daily', 'dbst_daily_audit_report');
                    $message = '✅ Reporte diario activado.';
                } else {
                    wp_clear_scheduled_hook('dbst_daily_audit_report');
                    $message = '⏸️ Reporte diario desactivado.';
                }
                $message_type = 'success';
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
        <h1>🛡️ DB-SafeTrigger v1.1.0</h1>
        <p><strong>Sistema de Auditoría y Trazabilidad de Base de Datos para WordPress</strong></p>
        
        <h2 class="nav-tab-wrapper">
            <a href="?page=db-safetrigger&tab=status" class="nav-tab <?php echo $active_tab == 'status' ? 'nav-tab-active' : ''; ?>">Estado del Sistema</a>
            <a href="?page=db-safetrigger&tab=triggers" class="nav-tab <?php echo $active_tab == 'triggers' ? 'nav-tab-active' : ''; ?>">Gestión de Triggers</a>
            <a href="?page=db-safetrigger&tab=mailjet" class="nav-tab <?php echo $active_tab == 'mailjet' ? 'nav-tab-active' : ''; ?>">Configuración Mailjet</a>
            <a href="?page=db-safetrigger&tab=reports" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : ''; ?>">Reportes</a>
            <a href="?page=db-safetrigger&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">Logs de Auditoría</a>
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
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE 'log_auditoria'") === 'log_auditoria';
    
    // Estadísticas
    $total_logs = $table_exists ? $wpdb->get_var("SELECT COUNT(*) FROM log_auditoria") : 0;
    $logs_today = $table_exists ? $wpdb->get_var("SELECT COUNT(*) FROM log_auditoria WHERE DATE(event_time) = CURDATE()") : 0;
    
    // Verificar triggers
    $triggers = $wpdb->get_results("SHOW TRIGGERS");
    $db_triggers = array_filter($triggers, function($t) { return strpos($t->Trigger, 'trg_') === 0; });
    $trigger_count = count($db_triggers);
    
    ?>
    <div class="dbst-card">
        <h2>📊 Estado del Sistema</h2>
        <p>Información general sobre el estado del sistema de auditoría.</p>
        
        <div class="dbst-stat-grid">
            <div class="dbst-stat-box">
                <span class="dbst-stat-number"><?php echo $table_exists ? '✅' : '❌'; ?></span>
                <strong>Tabla de Auditoría</strong><br>
                <small><?php echo $table_exists ? 'Configurada correctamente' : 'No encontrada'; ?></small>
            </div>
            
            <div class="dbst-stat-box">
                <span class="dbst-stat-number"><?php echo $trigger_count; ?></span>
                <strong>Triggers Activos</strong><br>
                <small><?php echo $trigger_count > 0 ? 'Sistema funcionando' : 'Triggers no configurados'; ?></small>
            </div>
            
            <div class="dbst-stat-box">
                <span class="dbst-stat-number"><?php echo number_format($total_logs); ?></span>
                <strong>Total de Logs</strong><br>
                <small>Desde el inicio</small>
            </div>
            
            <div class="dbst-stat-box">
                <span class="dbst-stat-number"><?php echo number_format($logs_today); ?></span>
                <strong>Logs Hoy</strong><br>
                <small><?php echo date('Y-m-d'); ?></small>
            </div>
        </div>
        
        <?php if ($trigger_count > 0): ?>
            <h3>🔧 Triggers Configurados</h3>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 6px;">
                <?php foreach ($db_triggers as $trigger): ?>
                    <div style="margin: 5px 0; padding: 8px; background: white; border-radius: 3px;">
                        <strong><?php echo esc_html($trigger->Trigger); ?></strong> - 
                        Tabla: <?php echo esc_html($trigger->Table); ?> - 
                        Evento: <?php echo esc_html($trigger->Event); ?>
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
    
    ?>
    <div class="dbst-card">
        <h2>🔧 Gestión de Triggers</h2>
        <p>Administra los triggers de auditoría para las tablas de WordPress.</p>
        
        <div style="background: #e7f3ff; padding: 15px; border-left: 4px solid #2196f3; margin: 15px 0;">
            <h4>📝 Información sobre Triggers</h4>
            <p>Los triggers se crean automáticamente para las tablas <code>posts</code>, <code>users</code> y <code>comments</code>.</p>
            <p><strong>Eventos monitoreados:</strong> UPDATE y DELETE</p>
            <p><strong>Información capturada:</strong> Usuario WordPress, timestamp, tabla, acción y datos modificados.</p>
        </div>
        
        <div style="display: flex; gap: 10px; margin: 20px 0;">
            <a href="?page=db-safetrigger&tab=triggers&action=create_triggers&_wpnonce=<?php echo $nonce; ?>" 
               class="button button-primary">
                🚀 Crear/Actualizar Triggers
            </a>
            
            <?php if (count($db_triggers) > 0): ?>
                <a href="?page=db-safetrigger&tab=triggers&action=delete_triggers&_wpnonce=<?php echo $nonce; ?>" 
                   class="button button-secondary"
                   onclick="return confirm('¿Estás seguro de eliminar todos los triggers?')">
                    🗑️ Eliminar Triggers
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (count($db_triggers) > 0): ?>
            <h3>📋 Triggers Actuales</h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Nombre del Trigger</th>
                        <th>Tabla</th>
                        <th>Evento</th>
                        <th>Timing</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($db_triggers as $trigger): ?>
                        <tr>
                            <td><code><?php echo esc_html($trigger->Trigger); ?></code></td>
                            <td><strong><?php echo esc_html($trigger->Table); ?></strong></td>
                            <td><span class="dashicons dashicons-<?php echo $trigger->Event === 'UPDATE' ? 'edit' : 'trash'; ?>"></span> <?php echo esc_html($trigger->Event); ?></td>
                            <td><?php echo esc_html($trigger->Timing); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800;">
                <h4>⚠️ No hay triggers configurados</h4>
                <p>Haz clic en "Crear/Actualizar Triggers" para configurar el sistema de auditoría.</p>
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
        
        echo '<div class="notice notice-success is-dismissible"><p>✅ Configuración guardada correctamente.</p></div>';
    }
    
    $api_key = get_option('dbst_mailjet_api_key', '');
    $secret_key = get_option('dbst_mailjet_secret_key', '');
    $from_email = get_option('dbst_mailjet_from_email', '');
    $from_name = get_option('dbst_mailjet_from_name', '');
    $recipients = get_option('dbst_report_recipients', '');
    
    ?>
    <div class="dbst-card">
        <h2>📧 Configuración de Mailjet</h2>
        <p>Configura las credenciales de Mailjet para el envío de reportes por email.</p>
        
        <form method="post">
            <?php wp_nonce_field('dbst_mailjet'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="api_key">API Key</label>
                    </th>
                    <td>
                        <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                        <p class="description">Tu API Key de Mailjet</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="secret_key">Secret Key</label>
                    </th>
                    <td>
                        <input type="password" id="secret_key" name="secret_key" value="<?php echo esc_attr($secret_key); ?>" class="regular-text" />
                        <p class="description">Tu Secret Key de Mailjet</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="from_email">Email Remitente</label>
                    </th>
                    <td>
                        <input type="email" id="from_email" name="from_email" value="<?php echo esc_attr($from_email); ?>" class="regular-text" />
                        <p class="description">Email desde el cual se enviarán los reportes</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="from_name">Nombre Remitente</label>
                    </th>
                    <td>
                        <input type="text" id="from_name" name="from_name" value="<?php echo esc_attr($from_name); ?>" class="regular-text" />
                        <p class="description">Nombre que aparecerá como remitente</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="recipients">Destinatarios</label>
                    </th>
                    <td>
                        <textarea id="recipients" name="recipients" rows="4" class="large-text"><?php echo esc_textarea($recipients); ?></textarea>
                        <p class="description">Un email por línea. Estos usuarios recibirán los reportes.</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_mailjet" class="button-primary" value="💾 Guardar Configuración" />
            </p>
        </form>
    </div>
    <?php
}

/**
 * Pestaña de reportes
 */
function dbst_reports_tab($nonce) {
    $daily_report_enabled = get_option('dbst_daily_report_enabled', 1);
    
    ?>
    <div class="dbst-card">
        <h2>📈 Gestión de Reportes</h2>
        <p>Configura y administra los reportes automáticos del sistema de auditoría.</p>
        
        <div style="background: #f0f8f0; padding: 15px; border-left: 4px solid #46b450; margin: 15px 0;">
            <h4>📅 Reporte Diario Automático</h4>
            <p><strong>Estado:</strong> <?php echo $daily_report_enabled ? '✅ Activado' : '❌ Desactivado'; ?></p>
            <p>Los reportes se envían diariamente a las direcciones configuradas en Mailjet.</p>
            
            <a href="?page=db-safetrigger&tab=reports&action=toggle_daily_report&_wpnonce=<?php echo $nonce; ?>" 
               class="button button-primary">
                <?php echo $daily_report_enabled ? '⏸️ Desactivar' : '▶️ Activar'; ?> Reporte Diario
            </a>
        </div>
    </div>
    <?php
}

/**
 * Pestaña de logs de auditoría
 */
function dbst_logs_tab() {
    global $wpdb;
    
    // Verificar si la tabla existe
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE 'log_auditoria'") === 'log_auditoria';
    
    if (!$table_exists) {
        echo '<div class="dbst-card">';
        echo '<h2>❌ Tabla de Auditoría No Encontrada</h2>';
        echo '<p>La tabla log_auditoria no existe. Por favor, activa el plugin o crea la tabla manualmente.</p>';
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
    $count_query = "SELECT COUNT(*) FROM log_auditoria l WHERE $where_clause";
    $total_logs = $wpdb->get_var(!empty($where_params) ? $wpdb->prepare($count_query, $where_params) : $count_query);
    
    // Obtener logs
    $query = "
        SELECT l.*, u.display_name, u.user_login
        FROM log_auditoria l
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
        FROM log_auditoria l
        INNER JOIN {$wpdb->users} u ON l.wp_user_id = u.ID
        ORDER BY u.display_name
    ");
    
    // Calcular número de páginas
    $total_pages = ceil($total_logs / $per_page);
    
    ?>
    <div class="dbst-card">
        <h2>📜 Logs de Auditoría</h2>
        <p>Registro completo de cambios detectados en las tablas monitoreadas con información de usuario WordPress.</p>
        
        <!-- Filtros -->
        <div style="background: #f9f9f9; padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <form method="get" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                <input type="hidden" name="page" value="db-safetrigger">
                <input type="hidden" name="tab" value="logs">
                
                <div>
                    <label for="user_id"><strong>👤 Usuario WordPress:</strong></label><br>
                    <select name="user_id" id="user_id" style="min-width: 150px;">
                        <option value="0">Todos los usuarios</option>
                        <?php foreach($users_with_logs as $user): ?>
                            <option value="<?php echo $user->ID; ?>" <?php selected($user_filter, $user->ID); ?>>
                                <?php echo esc_html($user->display_name . ' (' . $user->user_login . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="action_filter"><strong>🔄 Acción:</strong></label><br>
                    <select name="action_filter" id="action_filter" style="min-width: 120px;">
                        <option value="">Todas las acciones</option>
                        <option value="UPDATE" <?php selected($action_filter, 'UPDATE'); ?>>UPDATE</option>
                        <option value="DELETE" <?php selected($action_filter, 'DELETE'); ?>>DELETE</option>
                    </select>
                </div>
                
                <div>
                    <label for="table_filter"><strong>🗂️ Tabla:</strong></label><br>
                    <select name="table_filter" id="table_filter" style="min-width: 120px;">
                        <option value="">Todas las tablas</option>
                        <option value="posts" <?php selected($table_filter, 'posts'); ?>>Posts</option>
                        <option value="users" <?php selected($table_filter, 'users'); ?>>Users</option>
                        <option value="comments" <?php selected($table_filter, 'comments'); ?>>Comments</option>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="button button-primary">🔍 Filtrar</button>
                    <a href="?page=db-safetrigger&tab=logs" class="button">🔄 Limpiar</a>
                </div>
            </form>
        </div>
        
        <!-- Resultados -->
        <div style="background: white; border: 1px solid #ddd; border-radius: 4px;">
            <div style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #ddd;">
                <strong>📊 Mostrando <?php echo count($logs); ?> de <?php echo number_format($total_logs); ?> registros</strong>
                <?php if ($total_pages > 1): ?>
                    | Página <?php echo $page; ?> de <?php echo $total_pages; ?>
                <?php endif; ?>
            </div>
            
            <?php if (empty($logs)): ?>
                <div style="padding: 40px; text-align: center; color: #666;">
                    📭 No se encontraron logs con los filtros aplicados.
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped" style="border: none;">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Fecha y Hora</th>
                            <th style="width: 150px;">Usuario WordPress</th>
                            <th style="width: 100px;">Tabla</th>
                            <th style="width: 80px;">Acción</th>
                            <th style="width: 80px;">ID Registro</th>
                            <th>Usuario MySQL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td style="font-family: monospace; font-size: 11px;">
                                    <?php echo date('Y-m-d H:i:s', strtotime($log->event_time)); ?>
                                </td>
                                <td>
                                    <?php if ($log->wp_user_id && $log->display_name): ?>
                                        <strong style="color: #2271b1;">
                                            👤 <?php echo esc_html($log->display_name); ?>
                                        </strong>
                                        <br><small style="color: #666;">(@<?php echo esc_html($log->user_login); ?>)</small>
                                    <?php else: ?>
                                        <span style="color: #d63384;">❌ Sistema/No identificado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code style="background: #f1f1f1; padding: 2px 4px; border-radius: 3px;">
                                        <?php 
                                        $table_parts = explode('_', $log->table_name);
                                        echo esc_html(end($table_parts)); 
                                        ?>
                                    </code>
                                </td>
                                <td>
                                    <span class="dashicons dashicons-<?php echo $log->action === 'UPDATE' ? 'edit' : 'trash'; ?>" 
                                          style="color: <?php echo $log->action === 'UPDATE' ? '#2196f3' : '#f44336'; ?>;"></span>
                                    <strong><?php echo $log->action; ?></strong>
                                </td>
                                <td style="font-family: monospace;">
                                    <strong><?php echo esc_html($log->pk_value); ?></strong>
                                </td>
                                <td style="font-family: monospace; font-size: 11px; color: #666;">
                                    <?php echo esc_html($log->db_user ?: 'N/A'); ?>
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
        'DBST_Installer' => 'inc/class-dbst-installer.php'
    );
    
    foreach ($classes as $class_name => $file_path) {
        $full_path = plugin_dir_path(__FILE__) . $file_path;
        if (file_exists($full_path) && !class_exists($class_name)) {
            require_once $full_path;
        }
    }
}

// Cargar clases al inicio
add_action('init', 'dbst_load_classes');

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
    
    $results = array();
    $results[] = "🔍 <strong>INICIANDO CREACIÓN DE TRIGGERS v1.1.0</strong>";
    $results[] = "📋 Prefijo detectado: <code>$prefix</code>";
    
    // Verificar que la tabla log_auditoria existe
    $audit_table_exists = $wpdb->get_var("SHOW TABLES LIKE 'log_auditoria'") === 'log_auditoria';
    if (!$audit_table_exists) {
        $results[] = "❌ ERROR: Tabla 'log_auditoria' no existe";
        return array('type' => 'error', 'message' => implode('<br>', $results));
    }
    $results[] = "✅ Tabla 'log_auditoria' confirmada";
    
    // Verificar campo wp_user_id en tabla de auditoría
    $columns = $wpdb->get_results("DESCRIBE log_auditoria");
    $has_wp_user_id = false;
    foreach ($columns as $column) {
        if ($column->Field === 'wp_user_id') {
            $has_wp_user_id = true;
            break;
        }
    }
    
    if (!$has_wp_user_id) {
        $results[] = "⚠️ Campo wp_user_id no existe - agregando automáticamente...";
        $add_column = $wpdb->query("ALTER TABLE log_auditoria ADD COLUMN wp_user_id BIGINT UNSIGNED NULL AFTER db_user");
        
        if ($add_column !== false) {
            $results[] = "✅ Campo wp_user_id agregado correctamente";
            $wpdb->query("ALTER TABLE log_auditoria ADD INDEX idx_wp_user_time (wp_user_id, event_time)");
        } else {
            $results[] = "❌ Error agregando campo: " . $wpdb->last_error;
            return array('type' => 'error', 'message' => implode('<br>', $results));
        }
    } else {
        $results[] = "✅ Campo wp_user_id confirmado en tabla de auditoría";
    }
    
    // Configurar captura de usuario si la clase existe
    if (class_exists('DBST_Session')) {
        DBST_Session::force_set_user();
        $results[] = "✅ Sistema de captura de usuario activado";
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
            $results[] = "⚠️ Tabla $full_table_name no existe - saltando";
            continue;
        }
        
        $results[] = "🔧 <strong>Procesando tabla: $full_table_name</strong>";
        
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
            $results[] = "✅ Trigger UPDATE creado: $trigger_name_update";
            $created_count++;
        } else {
            $results[] = "❌ Error creando trigger UPDATE: " . ($wpdb->last_error ?: 'Error SQL desconocido');
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
            $results[] = "✅ Trigger DELETE creado: $trigger_name_delete";
            $created_count++;
        } else {
            $results[] = "❌ Error creando trigger DELETE: " . ($wpdb->last_error ?: 'Error SQL desconocido');
            $error_count++;
        }
    }
    
    $results[] = "📊 <strong>RESUMEN FINAL:</strong>";
    $results[] = "✅ Triggers creados exitosamente: <strong>$created_count</strong>";
    if ($error_count > 0) {
        $results[] = "❌ Errores encontrados: <strong>$error_count</strong>";
    }
    
    // Verificar triggers creados
    $active_triggers = $wpdb->get_results("SHOW TRIGGERS");
    $db_triggers = array_filter($active_triggers, function($t) { return strpos($t->Trigger, 'trg_') === 0; });
    $results[] = "🔍 Triggers activos en base de datos: <strong>" . count($db_triggers) . "</strong>";
    
    if (count($db_triggers) > 0) {
        $results[] = "📋 <strong>Lista de triggers activos:</strong>";
        foreach ($db_triggers as $trigger) {
            $results[] = "• $trigger->Trigger → $trigger->Table ($trigger->Event)";
        }
    }
    
    $message = implode('<br>', $results);
    $type = ($error_count === 0 && $created_count > 0) ? 'success' : 'error';
    
    return array('type' => $type, 'message' => $message);
}
?>