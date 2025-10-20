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
            <h1>🔒 DB-SafeTrigger</h1>
            <p>Plugin de Trazabilidad y Auditoría a Nivel de Base de Datos para WordPress</p>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=db-safetrigger&tab=status" class="nav-tab <?php echo $active_tab == 'status' ? 'nav-tab-active' : ''; ?>">
                    Estado del Sistema
                </a>
                <a href="?page=db-safetrigger&tab=mailjet" class="nav-tab <?php echo $active_tab == 'mailjet' ? 'nav-tab-active' : ''; ?>">
                    Configuración Mailjet
                </a>
                <a href="?page=db-safetrigger&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">
                    Configuración General
                </a>
                <a href="?page=db-safetrigger&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">
                    Logs Recientes
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
                $btn.prop('disabled', true).text('Enviando...');
                
                $.post(ajaxurl, {
                    action: 'dbst_test_email',
                    nonce: '<?php echo wp_create_nonce('dbst_admin_nonce'); ?>'
                }, function(response) {
                    if(response.success) {
                        alert('✅ Email de prueba enviado correctamente');
                    } else {
                        alert('❌ Error: ' + response.data);
                    }
                }).always(function() {
                    $btn.prop('disabled', false).text('Enviar Email de Prueba');
                });
            });
            
            // Verify triggers
            $('#dbst-verify-triggers').click(function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('Verificando...');
                
                $.post(ajaxurl, {
                    action: 'dbst_verify_triggers',
                    nonce: '<?php echo wp_create_nonce('dbst_admin_nonce'); ?>'
                }, function(response) {
                    if(response.success) {
                        location.reload();
                    } else {
                        alert('❌ Error: ' + response.data);
                    }
                }).always(function() {
                    $btn.prop('disabled', false).text('Verificar Triggers');
                });
            });
            
            // Test Mailjet
            $('#dbst-test-mailjet').click(function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('Probando...');
                
                $.post(ajaxurl, {
                    action: 'dbst_test_mailjet',
                    nonce: '<?php echo wp_create_nonce('dbst_admin_nonce'); ?>'
                }, function(response) {
                    if(response.success) {
                        alert('✅ Conexión Mailjet exitosa: ' + response.data);
                    } else {
                        alert('❌ Error Mailjet: ' + response.data);
                    }
                }).always(function() {
                    $btn.prop('disabled', false).text('Probar Conexión');
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
            <h2>🔍 Estado del Sistema</h2>
            
            <div class="dbst-status-grid">
                <div>
                    <h3>Base de Datos</h3>
                    <div class="dbst-status-item">
                        <span>Tabla log_auditoria:</span>
                        <span class="<?php echo $system_status['table_exists'] ? 'dbst-status-ok' : 'dbst-status-error'; ?>">
                            <?php echo $system_status['table_exists'] ? '✅ Existe' : '❌ No existe'; ?>
                        </span>
                    </div>
                    <div class="dbst-status-item">
                        <span>Soporte JSON:</span>
                        <span class="<?php echo $system_status['json_support'] ? 'dbst-status-ok' : 'dbst-status-warning'; ?>">
                            <?php echo $system_status['json_support'] ? '✅ Disponible' : '⚠️ LONGTEXT'; ?>
                        </span>
                    </div>
                    <div class="dbst-status-item">
                        <span>Privilegios TRIGGER:</span>
                        <span class="<?php echo $system_status['trigger_privileges'] ? 'dbst-status-ok' : 'dbst-status-error'; ?>">
                            <?php echo $system_status['trigger_privileges'] ? '✅ Disponibles' : '❌ Sin privilegios'; ?>
                        </span>
                    </div>
                    <div class="dbst-status-item">
                        <span>Total registros hoy:</span>
                        <span class="dbst-status-ok"><?php echo $system_status['today_events']; ?></span>
                    </div>
                </div>
                
                <div>
                    <h3>Triggers Activos</h3>
                    <?php foreach($system_status['triggers'] as $trigger => $status): ?>
                    <div class="dbst-status-item">
                        <span><?php echo esc_html($trigger); ?>:</span>
                        <span class="<?php echo $status ? 'dbst-status-ok' : 'dbst-status-error'; ?>">
                            <?php echo $status ? '✅ Activo' : '❌ Inactivo'; ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="dbst-status-grid">
                <div>
                    <h3>Sistema de Emails</h3>
                    <div class="dbst-status-item">
                        <span>Mailjet configurado:</span>
                        <span class="<?php echo $system_status['mailjet_configured'] ? 'dbst-status-ok' : 'dbst-status-warning'; ?>">
                            <?php echo $system_status['mailjet_configured'] ? '✅ Sí' : '⚠️ No'; ?>
                        </span>
                    </div>
                    <div class="dbst-status-item">
                        <span>Último reporte:</span>
                        <span><?php echo $system_status['last_report'] ?: 'Nunca'; ?></span>
                    </div>
                    <div class="dbst-status-item">
                        <span>Método usado:</span>
                        <span><?php echo $system_status['email_method'] ?: 'N/A'; ?></span>
                    </div>
                    <div class="dbst-status-item">
                        <span>Próximo cron:</span>
                        <span><?php echo $system_status['next_cron'] ? date('Y-m-d H:i:s', $system_status['next_cron']) : 'No programado'; ?></span>
                    </div>
                </div>
                
                <div>
                    <h3>Errores y Alertas</h3>
                    <?php if($system_status['last_sql_error']): ?>
                    <div class="notice notice-error">
                        <p><strong>Último error SQL:</strong></p>
                        <code><?php echo esc_html($system_status['last_sql_error']); ?></code>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($system_status['last_mailjet_error']): ?>
                    <div class="notice notice-error">
                        <p><strong>Último error Mailjet:</strong></p>
                        <code><?php echo esc_html($system_status['last_mailjet_error']); ?></code>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!$system_status['last_sql_error'] && !$system_status['last_mailjet_error']): ?>
                    <div class="notice notice-success">
                        <p>✅ Sin errores reportados</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="dbst-button-group">
                <button id="dbst-test-email" class="button button-secondary">Enviar Email de Prueba</button>
                <button id="dbst-verify-triggers" class="button button-secondary">Verificar Triggers</button>
                <a href="<?php echo admin_url('tools.php?page=db-safetrigger&tab=logs'); ?>" class="button">Ver Logs Detallados</a>
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
            
            echo '<div class="notice notice-success"><p>Configuración guardada correctamente.</p></div>';
        }
        ?>
        <div class="dbst-status-card">
            <h2>📧 Configuración Mailjet API v3.1</h2>
            <p>Configure sus credenciales de Mailjet para mejorar la entrega de emails de auditoría.</p>
            
            <form method="post">
                <?php wp_nonce_field('dbst_mailjet_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">API Key (Pública)</th>
                        <td>
                            <input type="text" 
                                   name="dbst_mailjet_api_key" 
                                   value="<?php echo esc_attr(get_option('dbst_mailjet_api_key')); ?>" 
                                   class="regular-text" />
                            <p class="description">Obténgala en <a href="https://app.mailjet.com/account/api_keys" target="_blank">Mailjet API Keys</a></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Secret Key (Privada)</th>
                        <td>
                            <input type="password" 
                                   name="dbst_mailjet_secret_key" 
                                   value="<?php echo esc_attr(get_option('dbst_mailjet_secret_key')); ?>" 
                                   class="regular-text" />
                            <p class="description">Mantenga esta clave segura y privada</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Email Remitente</th>
                        <td>
                            <input type="email" 
                                   name="dbst_mailjet_from_email" 
                                   value="<?php echo esc_attr(get_option('dbst_mailjet_from_email')); ?>" 
                                   class="regular-text" />
                            <p class="description">Debe ser un email verificado en Mailjet</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Nombre Remitente</th>
                        <td>
                            <input type="text" 
                                   name="dbst_mailjet_from_name" 
                                   value="<?php echo esc_attr(get_option('dbst_mailjet_from_name', get_bloginfo('name'))); ?>" 
                                   class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Modo Sandbox</th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="dbst_mailjet_sandbox_mode" 
                                       value="1" 
                                       <?php checked(get_option('dbst_mailjet_sandbox_mode'), 1); ?> />
                                Activar modo de prueba (no envía emails reales)
                            </label>
                        </td>
                    </tr>
                </table>
                
                <div class="dbst-button-group">
                    <?php submit_button('Guardar Configuración', 'primary', 'submit', false); ?>
                    <button type="button" id="dbst-test-mailjet" class="button button-secondary">Probar Conexión</button>
                </div>
            </form>
            
            <div class="dbst-status-card">
                <h3>🔗 Información de la API</h3>
                <p>DB-SafeTrigger utiliza <strong>Mailjet Send API v3.1</strong> con las siguientes características:</p>
                <ul>
                    <li>✅ Mejor reporte de errores y experiencia de desarrollador</li>
                    <li>✅ CustomID y CustomCampaign para tracking</li>
                    <li>✅ Headers personalizados para identificación</li>
                    <li>✅ URLTags automático para analytics (UTM)</li>
                    <li>✅ Modo Sandbox para pruebas sin envío real</li>
                </ul>
                
                <p><strong>Endpoint utilizado:</strong> <code>https://api.mailjet.com/v3.1/send</code></p>
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
            
            echo '<div class="notice notice-success"><p>Configuración guardada correctamente.</p></div>';
        }
        ?>
        <div class="dbst-status-card">
            <h2>⚙️ Configuración General</h2>
            
            <form method="post">
                <?php wp_nonce_field('dbst_general_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Email del Administrador</th>
                        <td>
                            <input type="email" 
                                   name="dbst_admin_email" 
                                   value="<?php echo esc_attr(get_option('dbst_admin_email', get_option('admin_email'))); ?>" 
                                   class="regular-text" />
                            <p class="description">Email donde se enviarán los reportes diarios</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Reporte Diario</th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="dbst_daily_report_enabled" 
                                       value="1" 
                                       <?php checked(get_option('dbst_daily_report_enabled', 1), 1); ?> />
                                Enviar reporte diario de auditoría
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
     * Tab de logs recientes
     */
    private function logs_tab() {
        global $wpdb;
        
        $logs = $wpdb->get_results("
            SELECT id, event_time, table_name, action, pk_value, db_user, client_host
            FROM log_auditoria 
            ORDER BY id DESC 
            LIMIT 50
        ");
        ?>
        <div class="dbst-status-card">
            <h2>📋 Logs de Auditoría Recientes</h2>
            
            <?php if(empty($logs)): ?>
                <p>No hay registros de auditoría disponibles.</p>
            <?php else: ?>
                <table class="dbst-log-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha/Hora</th>
                            <th>Tabla</th>
                            <th>Acción</th>
                            <th>PK</th>
                            <th>Usuario BD</th>
                            <th>Host</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                        <tr>
                            <td><?php echo $log->id; ?></td>
                            <td><?php echo $log->event_time; ?></td>
                            <td><?php echo esc_html($log->table_name); ?></td>
                            <td>
                                <span style="color: <?php echo $log->action === 'DELETE' ? '#d63638' : '#2271b1'; ?>">
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
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Obtener estado del sistema
     */
    private function get_system_status() {
        global $wpdb;
        
        // Verificar tabla
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE 'log_auditoria'") === 'log_auditoria';
        
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
                FROM log_auditoria 
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