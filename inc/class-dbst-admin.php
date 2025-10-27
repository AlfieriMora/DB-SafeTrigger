<?php
/**
 * Clase DBST_Admin - Panel de administración mínimo según especificación
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DBST_Admin {
    
    /**
     * Inicializar admin
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_dbst_test_email', array($this, 'ajax_test_email'));
        add_action('wp_ajax_dbst_verify_triggers', array($this, 'ajax_verify_triggers'));
        add_action('wp_ajax_dbst_test_mailjet', array($this, 'ajax_test_mailjet'));
    }
    
    /**
     * Agregar menú en Ajustes según especificación
     */
    public function add_admin_menu() {
        add_options_page(
            'DB-SafeTrigger',
            'DB-SafeTrigger',
            'manage_options',
            'db-safetrigger',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        // Configuraciones de Mailjet
        register_setting('dbst_mailjet', 'dbst_mailjet_api_key');
        register_setting('dbst_mailjet', 'dbst_mailjet_secret_key');
        register_setting('dbst_mailjet', 'dbst_mailjet_from_email');
        register_setting('dbst_mailjet', 'dbst_mailjet_from_name');
        register_setting('dbst_mailjet', 'dbst_mailjet_sandbox_mode');
        
        // Configuraciones generales
        register_setting('dbst_general', 'dbst_admin_email');
        register_setting('dbst_general', 'dbst_daily_report_enabled');
    }
    
    /**
     * Página de administración principal
     */
    public function admin_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'status';
        ?>
        <div class="wrap">
            <h1><?php _e('🔒 DB-SafeTrigger v1.1.0', 'db-safetrigger'); ?></h1>
            <p><?php _e('Sistema de Auditoría y Trazabilidad de Base de Datos para WordPress', 'db-safetrigger'); ?></p>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=db-safetrigger&tab=status" class="nav-tab <?php echo $active_tab == 'status' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Estado del Sistema', 'db-safetrigger'); ?>
                </a>
                <a href="?page=db-safetrigger&tab=mailjet" class="nav-tab <?php echo $active_tab == 'mailjet' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Configuración Mailjet', 'db-safetrigger'); ?>
                </a>
                <a href="?page=db-safetrigger&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Configuración General', 'db-safetrigger'); ?>
                </a>
                <a href="?page=db-safetrigger&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Logs de Auditoría', 'db-safetrigger'); ?>
                </a>
            </nav>
            
            <div class="tab-content">
                <?php
                switch($active_tab) {
                    case 'mailjet':
                        $this->mailjet_tab();
                        break;
                    case 'settings':
                        $this->settings_tab();
                        break;
                    case 'logs':
                        $this->logs_tab();
                        break;
                    default:
                        $this->status_tab();
                        break;
                }
                ?>
            </div>
        </div>
        
        <style>
        .dbst-status-card {
            background: white;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .dbst-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .dbst-status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f1;
        }
        .dbst-status-item:last-child {
            border-bottom: none;
        }
        .dbst-status-ok {
            color: #2271b1;
            font-weight: 600;
        }
        .dbst-status-error {
            color: #d63638;
            font-weight: 600;
        }
        .dbst-status-warning {
            color: #dba617;
            font-weight: 600;
        }
        .dbst-button-group {
            margin: 15px 0;
        }
        .dbst-button-group .button {
            margin-right: 10px;
        }
        .dbst-log-table {
            width: 100%;
            border-collapse: collapse;
        }
        .dbst-log-table th,
        .dbst-log-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #c3c4c7;
        }
        .dbst-log-table th {
            background: #f6f7f7;
            font-weight: 600;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Test email
            $('#dbst-test-email').click(function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('<?php _e('Enviando...', 'db-safetrigger'); ?>');
                
                $.post(ajaxurl, {
                    action: 'dbst_test_email',
                    nonce: '<?php echo wp_create_nonce('dbst_admin_nonce'); ?>'
                }, function(response) {
                    if(response.success) {
                        alert('<?php _e('✅ Email de prueba enviado correctamente', 'db-safetrigger'); ?>');
                    } else {
                        alert('<?php _e('❌ Error:', 'db-safetrigger'); ?> ' + response.data);
                    }
                }).always(function() {
                    $btn.prop('disabled', false).text('<?php _e('Enviar Email de Prueba', 'db-safetrigger'); ?>');
                });
            });
            
            // Verify triggers
            $('#dbst-verify-triggers').click(function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('<?php _e('Verificando...', 'db-safetrigger'); ?>');
                
                $.post(ajaxurl, {
                    action: 'dbst_verify_triggers',
                    nonce: '<?php echo wp_create_nonce('dbst_admin_nonce'); ?>'
                }, function(response) {
                    if(response.success) {
                        location.reload();
                    } else {
                        alert('<?php _e('❌ Error:', 'db-safetrigger'); ?> ' + response.data);
                    }
                }).always(function() {
                    $btn.prop('disabled', false).text('<?php _e('Verificar Triggers', 'db-safetrigger'); ?>');
                });
            });
            
            // Test Mailjet
            $('#dbst-test-mailjet').click(function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('<?php _e('Probando...', 'db-safetrigger'); ?>');
                
                $.post(ajaxurl, {
                    action: 'dbst_test_mailjet',
                    nonce: '<?php echo wp_create_nonce('dbst_admin_nonce'); ?>'
                }, function(response) {
                    if(response.success) {
                        alert('<?php _e('✅ Conexión Mailjet exitosa:', 'db-safetrigger'); ?> ' + response.data);
                    } else {
                        alert('<?php _e('❌ Error Mailjet:', 'db-safetrigger'); ?> ' + response.data);
                    }
                }).always(function() {
                    $btn.prop('disabled', false).text('<?php _e('Probar Conexión', 'db-safetrigger'); ?>');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Tab de Estado del Sistema
     */
    private function status_tab() {
        $system_status = $this->get_system_status();
        ?>
        <div class="dbst-status-card">
            <h2><?php _e('🔍 Estado del Sistema', 'db-safetrigger'); ?></h2>
            <p><?php _e('Información general sobre el estado del sistema de auditoría.', 'db-safetrigger'); ?></p>
            
            <div class="dbst-status-grid">
                <div>
                    <h3><?php _e('Base de Datos', 'db-safetrigger'); ?></h3>
                    <div class="dbst-status-item">
                        <span><?php _e('Tabla de Auditoría', 'db-safetrigger'); ?>:</span>
                        <span class="<?php echo $system_status['table_exists'] ? 'dbst-status-ok' : 'dbst-status-error'; ?>">
                            <?php echo $system_status['table_exists'] ? __('✅ Configurada correctamente', 'db-safetrigger') : __('❌ No encontrada', 'db-safetrigger'); ?>
                        </span>
                    </div>
                    <div class="dbst-status-item">
                        <span><?php _e('Soporte JSON:', 'db-safetrigger'); ?></span>
                        <span class="<?php echo $system_status['json_support'] ? 'dbst-status-ok' : 'dbst-status-warning'; ?>">
                            <?php echo $system_status['json_support'] ? __('✅ Disponible', 'db-safetrigger') : __('⚠️ LONGTEXT', 'db-safetrigger'); ?>
                        </span>
                    </div>
                    <div class="dbst-status-item">
                        <span><?php _e('Privilegios TRIGGER:', 'db-safetrigger'); ?></span>
                        <span class="<?php echo $system_status['trigger_privileges'] ? 'dbst-status-ok' : 'dbst-status-error'; ?>">
                            <?php echo $system_status['trigger_privileges'] ? __('✅ Disponibles', 'db-safetrigger') : __('❌ Sin privilegios', 'db-safetrigger'); ?>
                        </span>
                    </div>
                    <div class="dbst-status-item">
                        <span><?php _e('Total de Logs', 'db-safetrigger'); ?>:</span>
                        <span class="dbst-status-ok"><?php echo $system_status['today_events']; ?></span>
                        <small><?php _e('Desde el inicio', 'db-safetrigger'); ?></small>
                    </div>
                </div>
                
                <div>
                    <h3><?php _e('Triggers Activos', 'db-safetrigger'); ?></h3>
                    <?php foreach($system_status['triggers'] as $trigger => $status): ?>
                    <div class="dbst-status-item">
                        <span><?php echo esc_html($trigger); ?>:</span>
                        <span class="<?php echo $status ? 'dbst-status-ok' : 'dbst-status-error'; ?>">
                            <?php echo $status ? __('✅ Activo', 'db-safetrigger') : __('❌ Inactivo', 'db-safetrigger'); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (array_sum($system_status['triggers']) === 0): ?>
                    <p class="description"><?php _e('Triggers no configurados', 'db-safetrigger'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="dbst-status-grid">
                <div>
                    <h3><?php _e('Sistema de Emails', 'db-safetrigger'); ?></h3>
                    <div class="dbst-status-item">
                        <span><?php _e('Mailjet configurado:', 'db-safetrigger'); ?></span>
                        <span class="<?php echo $system_status['mailjet_configured'] ? 'dbst-status-ok' : 'dbst-status-warning'; ?>">
                            <?php echo $system_status['mailjet_configured'] ? __('✅ Configurado', 'db-safetrigger') : __('⚠️ No configurado', 'db-safetrigger'); ?>
                        </span>
                    </div>
                    <div class="dbst-status-item">
                        <span><?php _e('Último reporte:', 'db-safetrigger'); ?></span>
                        <span><?php echo $system_status['last_report'] ?: __('Nunca', 'db-safetrigger'); ?></span>
                    </div>
                    <div class="dbst-status-item">
                        <span><?php _e('Método usado:', 'db-safetrigger'); ?></span>
                        <span><?php echo $system_status['email_method'] ?: 'N/A'; ?></span>
                    </div>
                    <div class="dbst-status-item">
                        <span><?php _e('Próximo cron:', 'db-safetrigger'); ?></span>
                        <span><?php echo $system_status['next_cron'] ? date('Y-m-d H:i:s', $system_status['next_cron']) : __('No programado', 'db-safetrigger'); ?></span>
                    </div>
                </div>
                
                <div>
                    <h3><?php _e('Errores y Alertas', 'db-safetrigger'); ?></h3>
                    <?php if($system_status['last_sql_error']): ?>
                    <div class="notice notice-error">
                        <p><strong><?php _e('Último error SQL:', 'db-safetrigger'); ?></strong></p>
                        <code><?php echo esc_html($system_status['last_sql_error']); ?></code>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($system_status['last_mailjet_error']): ?>
                    <div class="notice notice-error">
                        <p><strong><?php _e('Último error Mailjet:', 'db-safetrigger'); ?></strong></p>
                        <code><?php echo esc_html($system_status['last_mailjet_error']); ?></code>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!$system_status['last_sql_error'] && !$system_status['last_mailjet_error']): ?>
                    <div class="notice notice-success">
                        <p><?php _e('✅ Sin errores reportados', 'db-safetrigger'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="dbst-button-group">
                <button id="dbst-test-email" class="button button-secondary"><?php _e('Enviar Email de Prueba', 'db-safetrigger'); ?></button>
                <button id="dbst-verify-triggers" class="button button-secondary"><?php _e('Verificar Triggers', 'db-safetrigger'); ?></button>
                <a href="<?php echo admin_url('tools.php?page=db-safetrigger&tab=logs'); ?>" class="button"><?php _e('Ver Logs Detallados', 'db-safetrigger'); ?></a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Tab de configuración Mailjet
     */
    private function mailjet_tab() {
        if(isset($_POST['submit'])) {
            check_admin_referer('dbst_mailjet_settings');
            
            update_option('dbst_mailjet_api_key', sanitize_text_field($_POST['dbst_mailjet_api_key']));
            update_option('dbst_mailjet_secret_key', sanitize_text_field($_POST['dbst_mailjet_secret_key']));
            update_option('dbst_mailjet_from_email', sanitize_email($_POST['dbst_mailjet_from_email']));
            update_option('dbst_mailjet_from_name', sanitize_text_field($_POST['dbst_mailjet_from_name']));
            update_option('dbst_mailjet_sandbox_mode', isset($_POST['dbst_mailjet_sandbox_mode']) ? 1 : 0);
            
            echo '<div class="notice notice-success"><p>' . __('✅ Configuración de Mailjet guardada correctamente.', 'db-safetrigger') . '</p></div>';
        }
        ?>
        <div class="dbst-status-card">
            <h2><?php _e('📧 Configuración Mailjet API v3.1', 'db-safetrigger'); ?></h2>
            <p><?php _e('Configura el sistema de envío de reportes por email usando Mailjet API.', 'db-safetrigger'); ?></p>
            
            <form method="post">
                <?php wp_nonce_field('dbst_mailjet_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('API Key', 'db-safetrigger'); ?></th>
                        <td>
                            <input type="text" 
                                   name="dbst_mailjet_api_key" 
                                   value="<?php echo esc_attr(get_option('dbst_mailjet_api_key')); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php printf(__('Obténgala en %s', 'db-safetrigger'), '<a href="https://app.mailjet.com/account/api_keys" target="_blank">Mailjet API Keys</a>'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Secret Key', 'db-safetrigger'); ?></th>
                        <td>
                            <input type="password" 
                                   name="dbst_mailjet_secret_key" 
                                   value="<?php echo esc_attr(get_option('dbst_mailjet_secret_key')); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php _e('Mantenga esta clave segura y privada', 'db-safetrigger'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Email Remitente', 'db-safetrigger'); ?></th>
                        <td>
                            <input type="email" 
                                   name="dbst_mailjet_from_email" 
                                   value="<?php echo esc_attr(get_option('dbst_mailjet_from_email')); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php _e('Debe ser un email verificado en Mailjet', 'db-safetrigger'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Nombre Remitente', 'db-safetrigger'); ?></th>
                        <td>
                            <input type="text" 
                                   name="dbst_mailjet_from_name" 
                                   value="<?php echo esc_attr(get_option('dbst_mailjet_from_name', get_bloginfo('name'))); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Modo Sandbox', 'db-safetrigger'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="dbst_mailjet_sandbox_mode" 
                                       value="1" 
                                       <?php checked(get_option('dbst_mailjet_sandbox_mode'), 1); ?> />
                                <?php _e('Activar modo de prueba (no envía emails reales)', 'db-safetrigger'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <div class="dbst-button-group">
                    <?php submit_button(__('Guardar Configuración', 'db-safetrigger'), 'primary', 'submit', false); ?>
                    <button type="button" id="dbst-test-mailjet" class="button button-secondary"><?php _e('Probar Conexión', 'db-safetrigger'); ?></button>
                </div>
            </form>
            
            <div class="dbst-status-card">
                <h3><?php _e('🔗 Información de la API', 'db-safetrigger'); ?></h3>
                <p><?php _e('DB-SafeTrigger utiliza Mailjet Send API v3.1 con las siguientes características:', 'db-safetrigger'); ?></p>
                <ul>
                    <li><?php _e('✅ Mejor reporte de errores y experiencia de desarrollador', 'db-safetrigger'); ?></li>
                    <li><?php _e('✅ CustomID y CustomCampaign para tracking', 'db-safetrigger'); ?></li>
                    <li><?php _e('✅ Headers personalizados para identificación', 'db-safetrigger'); ?></li>
                    <li><?php _e('✅ URLTags automático para analytics (UTM)', 'db-safetrigger'); ?></li>
                    <li><?php _e('✅ Modo Sandbox para pruebas sin envío real', 'db-safetrigger'); ?></li>
                </ul>
                
                <p><strong><?php _e('Endpoint utilizado:', 'db-safetrigger'); ?></strong> <code>https://api.mailjet.com/v3.1/send</code></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Tab de configuración general
     */
    private function settings_tab() {
        if(isset($_POST['submit'])) {
            check_admin_referer('dbst_general_settings');
            
            update_option('dbst_admin_email', sanitize_email($_POST['dbst_admin_email']));
            update_option('dbst_daily_report_enabled', isset($_POST['dbst_daily_report_enabled']) ? 1 : 0);
            
            echo '<div class="notice notice-success"><p>' . __('✅ Configuración guardada correctamente.', 'db-safetrigger') . '</p></div>';
        }
        ?>
        <div class="dbst-status-card">
            <h2><?php _e('⚙️ Configuración General', 'db-safetrigger'); ?></h2>
            
            <form method="post">
                <?php wp_nonce_field('dbst_general_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Email del Administrador', 'db-safetrigger'); ?></th>
                        <td>
                            <input type="email" 
                                   name="dbst_admin_email" 
                                   value="<?php echo esc_attr(get_option('dbst_admin_email', get_option('admin_email'))); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php _e('Email donde se enviarán los reportes diarios', 'db-safetrigger'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Reporte Diario', 'db-safetrigger'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="dbst_daily_report_enabled" 
                                       value="1" 
                                       <?php checked(get_option('dbst_daily_report_enabled', 1), 1); ?> />
                                <?php _e('Enviar reporte diario de auditoría', 'db-safetrigger'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Tab de logs recientes con información de usuario WordPress
     */
    private function logs_tab() {
        global $wpdb;
        $audit_table = $wpdb->prefix . 'BD_SafeTrigger';
        
        // Obtener filtros
        $user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $action_filter = isset($_GET['action_filter']) ? sanitize_text_field($_GET['action_filter']) : '';
        $table_filter = isset($_GET['table_filter']) ? sanitize_text_field($_GET['table_filter']) : '';
        
        // Construir WHERE clause
        $where_conditions = array('1=1');
        $where_params = array();
        
        if ($user_filter > 0) {
            $where_conditions[] = 'l.wp_user_id = %d';
            $where_params[] = $user_filter;
        }
        
        if (!empty($action_filter)) {
            $where_conditions[] = 'l.action = %s';
            $where_params[] = $action_filter;
        }
        
        if (!empty($table_filter)) {
            $where_conditions[] = 'l.table_name LIKE %s';
            $where_params[] = '%' . $table_filter . '%';
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Query optimizada con JOIN a wp_users
        $query = "
            SELECT 
                l.id, 
                l.event_time, 
                l.table_name, 
                l.action, 
                l.pk_value, 
                l.db_user,
                l.wp_user_id,
                l.client_host,
                u.user_login,
                u.display_name,
                u.user_email
            FROM `{$audit_table}` l
            LEFT JOIN {$wpdb->users} u ON l.wp_user_id = u.ID
            WHERE $where_clause
            ORDER BY l.id DESC 
            LIMIT 50
        ";
        
        if (!empty($where_params)) {
            $logs = $wpdb->get_results($wpdb->prepare($query, $where_params));
        } else {
            $logs = $wpdb->get_results($query);
        }
        
        // Obtener usuarios para filtro
        $users_with_logs = $wpdb->get_results("
            SELECT DISTINCT u.ID, u.user_login, u.display_name
            FROM `{$audit_table}` l
            INNER JOIN {$wpdb->users} u ON l.wp_user_id = u.ID
            ORDER BY u.display_name
        ");
        
        ?>
        <div class="dbst-status-card">
            <h2><?php _e('📋 Logs de Auditoría', 'db-safetrigger'); ?></h2>
            
            <!-- Filtros -->
            <div class="dbst-filters" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
                <form method="get" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                    <input type="hidden" name="page" value="db-safetrigger">
                    <input type="hidden" name="tab" value="logs">
                    
                    <div>
                        <label for="user_id"><strong><?php _e('Usuario WordPress:', 'db-safetrigger'); ?></strong></label><br>
                        <select name="user_id" id="user_id">
                            <option value="0"><?php _e('Todos los usuarios', 'db-safetrigger'); ?></option>
                            <?php foreach($users_with_logs as $user): ?>
                                <option value="<?php echo $user->ID; ?>" <?php selected($user_filter, $user->ID); ?>>
                                    <?php echo esc_html($user->display_name . ' (' . $user->user_login . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="action_filter"><strong><?php _e('Acción:', 'db-safetrigger'); ?></strong></label><br>
                        <select name="action_filter" id="action_filter">
                            <option value=""><?php _e('Todas las acciones', 'db-safetrigger'); ?></option>
                            <option value="UPDATE" <?php selected($action_filter, 'UPDATE'); ?>>UPDATE</option>
                            <option value="DELETE" <?php selected($action_filter, 'DELETE'); ?>>DELETE</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="table_filter"><strong><?php _e('Tabla:', 'db-safetrigger'); ?></strong></label><br>
                        <select name="table_filter" id="table_filter">
                            <option value=""><?php _e('Todas las tablas', 'db-safetrigger'); ?></option>
                            <option value="posts" <?php selected($table_filter, 'posts'); ?>><?php _e('Posts', 'db-safetrigger'); ?></option>
                            <option value="users" <?php selected($table_filter, 'users'); ?>><?php _e('Users', 'db-safetrigger'); ?></option>
                            <option value="comments" <?php selected($table_filter, 'comments'); ?>><?php _e('Comments', 'db-safetrigger'); ?></option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="button button-primary"><?php _e('Filtrar', 'db-safetrigger'); ?></button>
                        <a href="?page=db-safetrigger&tab=logs" class="button"><?php _e('Limpiar', 'db-safetrigger'); ?></a>
                    </div>
                </form>
            </div>
            
            <?php if(empty($logs)): ?>
                <p><?php _e('No se encontraron logs con los filtros aplicados.', 'db-safetrigger'); ?></p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="dbst-log-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th><?php _e('Fecha y Hora', 'db-safetrigger'); ?></th>
                                <th><?php _e('Usuario WordPress:', 'db-safetrigger'); ?></th>
                                <th><?php _e('Tabla:', 'db-safetrigger'); ?></th>
                                <th><?php _e('Acción:', 'db-safetrigger'); ?></th>
                                <th>PK</th>
                                <th><?php _e('Usuario BD', 'db-safetrigger'); ?></th>
                                <th>Host</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td><?php echo $log->id; ?></td>
                                <td><?php echo $log->event_time; ?></td>
                                <td>
                                    <?php if($log->wp_user_id): ?>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <?php echo get_avatar($log->wp_user_id, 24, '', '', array('class' => 'dbst-user-avatar')); ?>
                                            <div>
                                                <strong><?php echo esc_html($log->display_name ?: $log->user_login); ?></strong><br>
                                                <small style="color: #666;"><?php echo esc_html($log->user_login); ?></small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #999; font-style: italic;"><?php _e('Sistema', 'db-safetrigger'); ?>/<?php _e('No identificado', 'db-safetrigger'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="dbst-table-name">
                                        <?php echo esc_html(str_replace($wpdb->prefix, '', $log->table_name)); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: <?php echo $log->action === 'DELETE' ? '#d63638' : '#2271b1'; ?>; font-weight: 600;">
                                        <?php echo $log->action; ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($log->pk_value); ?></td>
                                <td><small><?php echo esc_html($log->db_user); ?></small></td>
                                <td><small><?php echo esc_html($log->client_host); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <p style="margin-top: 15px; color: #666;">
                    <small><?php printf(__('Mostrando los últimos %d registros que coinciden con los filtros.', 'db-safetrigger'), count($logs)); ?></small>
                </p>
            <?php endif; ?>
            
            <style>
            .dbst-user-avatar {
                border-radius: 50%;
                vertical-align: middle;
            }
            .dbst-table-name {
                font-family: monospace;
                background: #f0f0f1;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 12px;
            }
            .dbst-filters select {
                min-width: 150px;
            }
            .dbst-log-table {
                font-size: 13px;
            }
            .dbst-log-table td {
                vertical-align: top;
                padding: 12px 8px;
            }
            </style>
        </div>
        <?php
    }
    
    /**
     * Obtener estado del sistema
     */
    private function get_system_status() {
        global $wpdb;
        $audit_table = $wpdb->prefix . 'BD_SafeTrigger';
        
        // Verificar tabla
        $audit_table = $wpdb->prefix . 'BD_SafeTrigger';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $audit_table)) === $audit_table;
        
        // Verificar triggers
        $triggers = array(
            'trg_posts_bu' => false,
            'trg_posts_bd' => false,
            'trg_users_bu' => false,
            'trg_users_bd' => false,
            'trg_comments_bu' => false,
            'trg_comments_bd' => false
        );
        
        $active_triggers = $wpdb->get_results("SHOW TRIGGERS");
        foreach($active_triggers as $trigger) {
            if(isset($triggers[$trigger->Trigger])) {
                $triggers[$trigger->Trigger] = true;
            }
        }
        
        // Verificar privilegios TRIGGER
        $trigger_privileges = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM information_schema.USER_PRIVILEGES 
            WHERE PRIVILEGE_TYPE = 'TRIGGER' 
            AND GRANTEE LIKE CONCAT(\"'\", USER(), \"'%\")
        ") > 0;
        
        // Contar eventos de hoy
        $today_events = 0;
        if($table_exists) {
            $today_events = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM `{$audit_table}` 
                WHERE DATE(event_time) = CURDATE()
            ");
        }
        
        return array(
            'table_exists' => $table_exists,
            'json_support' => get_option('dbst_json_support', false),
            'trigger_privileges' => $trigger_privileges,
            'triggers' => $triggers,
            'today_events' => $today_events,
            'mailjet_configured' => $this->is_mailjet_configured(),
            'last_report' => get_option('dbst_last_report_sent'),
            'email_method' => get_option('dbst_last_email_method'),
            'next_cron' => wp_next_scheduled('dbst_daily_audit_report'),
            'last_sql_error' => get_option('dbst_last_sql_error'),
            'last_mailjet_error' => get_option('dbst_last_mailjet_error')
        );
    }
    
    /**
     * Verificar si Mailjet está configurado
     */
    private function is_mailjet_configured() {
        $api_key = get_option('dbst_mailjet_api_key');
        $secret_key = get_option('dbst_mailjet_secret_key');
        $from_email = get_option('dbst_mailjet_from_email');
        
        return !empty($api_key) && !empty($secret_key) && !empty($from_email);
    }
    
    /**
     * AJAX: Test email
     */
    public function ajax_test_email() {
        check_ajax_referer('dbst_admin_nonce', 'nonce');
        
        if(!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        require_once DB_SAFETRIGGER_PLUGIN_DIR . 'inc/class-dbst-cron.php';
        $cron = new DBST_Cron();
        
        $result = $cron->send_test_report();
        
        if($result) {
            wp_send_json_success('Email enviado correctamente');
        } else {
            $error = get_option('dbst_last_mailjet_error') ?: 'Error desconocido';
            wp_send_json_error($error);
        }
    }
    
    /**
     * AJAX: Verify triggers
     */
    public function ajax_verify_triggers() {
        check_ajax_referer('dbst_admin_nonce', 'nonce');
        
        if(!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        require_once DB_SAFETRIGGER_PLUGIN_DIR . 'inc/class-dbst-installer.php';
        $installer = new DBST_Installer();
        
        $tables = array('posts', 'users', 'comments');
        $success = true;
        
        foreach($tables as $table) {
            if(!$installer->create_table_triggers($table)) {
                $success = false;
            }
        }
        
        if($success) {
            wp_send_json_success('Triggers verificados y recreados correctamente');
        } else {
            $error = get_option('dbst_last_sql_error') ?: 'Error verificando triggers';
            wp_send_json_error($error);
        }
    }
    
    /**
     * AJAX: Test Mailjet connection
     */
    public function ajax_test_mailjet() {
        check_ajax_referer('dbst_admin_nonce', 'nonce');
        
        if(!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        if(!$this->is_mailjet_configured()) {
            wp_send_json_error('Mailjet no está configurado completamente');
        }
        
        // Test básico con modo sandbox
        $api_key = get_option('dbst_mailjet_api_key');
        $secret_key = get_option('dbst_mailjet_secret_key');
        $from_email = get_option('dbst_mailjet_from_email');
        
        $test_payload = array(
            'Messages' => array(
                array(
                    'From' => array(
                        'Email' => $from_email,
                        'Name' => 'DB-SafeTrigger Test'
                    ),
                    'To' => array(
                        array(
                            'Email' => 'test@example.com',
                            'Name' => 'Test'
                        )
                    ),
                    'Subject' => 'Test Connection',
                    'HTMLPart' => '<p>Test</p>'
                )
            ),
            'SandboxMode' => true
        );
        
        $response = wp_remote_post('https://api.mailjet.com/v3.1/send', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $secret_key),
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($test_payload),
            'timeout' => 15
        ));
        
        if(is_wp_error($response)) {
            wp_send_json_error('Error de conexión: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if($code === 200) {
            $data = json_decode($body, true);
            if(isset($data['Messages'][0]['Status']) && $data['Messages'][0]['Status'] === 'success') {
                wp_send_json_success('Conexión exitosa (modo sandbox)');
            } else {
                $errors = isset($data['Messages'][0]['Errors']) ? $data['Messages'][0]['Errors'] : array();
                $error_msg = 'Error de validación: ';
                foreach($errors as $error) {
                    $error_msg .= $error['ErrorMessage'] . ' ';
                }
                wp_send_json_error($error_msg);
            }
        } else {
            wp_send_json_error("HTTP $code: $body");
        }
    }
}