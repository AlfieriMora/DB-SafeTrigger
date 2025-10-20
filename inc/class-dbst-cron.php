<?php
/**
 * Clase DBST_Cron - Manejo de resumen diario y envío de emails
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DBST_Cron {
    
    /**
     * Endpoint de Mailjet API
     */
    const MAILJET_API_URL = 'https://api.mailjet.com/v3.1/send';
    
    /**
     * Hook del cron diario
     */
    const DAILY_HOOK = 'dbst_daily_audit_report';
    
    /**
     * Inicializar sistema de cron
     */
    public function init() {
        // Registrar hook del cron
        add_action(self::DAILY_HOOK, array($this, 'send_daily_report'));
        
        // Programar tarea diaria si no existe
        if (!wp_next_scheduled(self::DAILY_HOOK)) {
            wp_schedule_event(time(), 'daily', self::DAILY_HOOK);
        }
    }
    
    /**
     * Enviar resumen diario de auditoría
     */
    public function send_daily_report() {
        $report_data = $this->generate_daily_report();
        
        if (!$report_data) {
            // No hay actividad para reportar
            return;
        }
        
        $email_content = $this->build_email_content($report_data);
        $success = $this->send_email($email_content);
        
        // Registrar resultado
        update_option('dbst_last_report_sent', current_time('mysql'));
        update_option('dbst_last_report_success', $success);
        
        if (!$success) {
            error_log('DB-SafeTrigger: Error enviando reporte diario');
        }
    }
    
    /**
     * Generar datos del reporte diario
     */
    private function generate_daily_report() {
        global $wpdb;
        
        $today = current_time('Y-m-d');
        
        // Consulta principal según especificación
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                table_name,
                action,
                COUNT(*) as total_events,
                MIN(event_time) as first_event,
                MAX(event_time) as last_event
            FROM log_auditoria 
            WHERE DATE(event_time) = %s
            GROUP BY table_name, action
            ORDER BY total_events DESC, table_name, action
        ", $today));
        
        if (empty($results)) {
            return false;
        }
        
        // Obtener estadísticas adicionales
        $total_events = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM log_auditoria 
            WHERE DATE(event_time) = %s
        ", $today));
        
        $unique_tables = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT table_name) 
            FROM log_auditoria 
            WHERE DATE(event_time) = %s
        ", $today));
        
        return array(
            'date' => $today,
            'total_events' => $total_events,
            'unique_tables' => $unique_tables,
            'details' => $results,
            'summary' => $this->generate_summary_stats($results)
        );
    }
    
    /**
     * Generar estadísticas de resumen
     */
    private function generate_summary_stats($results) {
        $stats = array(
            'updates' => 0,
            'deletes' => 0,
            'tables_affected' => array()
        );
        
        foreach ($results as $row) {
            if ($row->action === 'UPDATE') {
                $stats['updates'] += $row->total_events;
            } elseif ($row->action === 'DELETE') {
                $stats['deletes'] += $row->total_events;
            }
            
            $stats['tables_affected'][] = $row->table_name;
        }
        
        $stats['tables_affected'] = array_unique($stats['tables_affected']);
        
        return $stats;
    }
    
    /**
     * Construir contenido HTML del email
     */
    private function build_email_content($data) {
        $site_name = get_bloginfo('name');
        $site_url = home_url();
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Reporte Diario DB-SafeTrigger</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0073aa; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
                .stat-card { background: white; padding: 15px; border-radius: 5px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .stat-number { font-size: 24px; font-weight: bold; color: #0073aa; }
                .table-details { background: white; border-radius: 5px; padding: 15px; margin: 15px 0; }
                .table-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                .alert { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔒 DB-SafeTrigger</h1>
                    <h2>Reporte Diario de Auditoría</h2>
                    <p><?php echo $data['date']; ?> | <?php echo $site_name; ?></p>
                </div>
                
                <div class="content">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $data['total_events']; ?></div>
                            <div>Total Eventos</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $data['summary']['updates']; ?></div>
                            <div>Actualizaciones</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $data['summary']['deletes']; ?></div>
                            <div>Eliminaciones</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $data['unique_tables']; ?></div>
                            <div>Tablas Afectadas</div>
                        </div>
                    </div>
                    
                    <?php if ($data['summary']['deletes'] > 0): ?>
                    <div class="alert">
                        <strong>⚠️ Atención:</strong> Se detectaron <?php echo $data['summary']['deletes']; ?> eliminaciones en la base de datos.
                    </div>
                    <?php endif; ?>
                    
                    <div class="table-details">
                        <h3>Detalle por Tabla y Acción</h3>
                        <?php foreach ($data['details'] as $detail): ?>
                        <div class="table-row">
                            <div>
                                <strong><?php echo esc_html($detail->table_name); ?></strong> 
                                <span style="color: <?php echo $detail->action === 'DELETE' ? '#dc3545' : '#28a745'; ?>">
                                    [<?php echo $detail->action; ?>]
                                </span>
                            </div>
                            <div>
                                <strong><?php echo $detail->total_events; ?></strong> eventos
                                <br><small><?php echo $detail->first_event; ?> - <?php echo $detail->last_event; ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="background: white; padding: 15px; border-radius: 5px; margin-top: 20px;">
                        <h4>Acciones Recomendadas</h4>
                        <ul>
                            <li>Revisar logs detallados en el panel de administración</li>
                            <li>Verificar que las eliminaciones sean autorizadas</li>
                            <li>Contactar al administrador si detecta actividad sospechosa</li>
                        </ul>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Generado por DB-SafeTrigger v<?php echo DB_SAFETRIGGER_VERSION; ?></p>
                    <p><a href="<?php echo $site_url; ?>"><?php echo $site_name; ?></a></p>
                    <p>Para desactivar estos reportes, accede al panel de administración del plugin.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Enviar email usando Mailjet o wp_mail como fallback
     */
    private function send_email($content) {
        $admin_email = get_option('dbst_admin_email', get_option('admin_email'));
        $site_name = get_bloginfo('name');
        $subject = "[DB-SafeTrigger] Reporte Diario - " . current_time('Y-m-d') . " - " . $site_name;
        
        // Intentar envío con Mailjet si está configurado
        if ($this->is_mailjet_configured()) {
            $success = $this->send_via_mailjet($admin_email, $subject, $content);
            if ($success) {
                update_option('dbst_last_email_method', 'mailjet');
                return true;
            }
        }
        
        // Fallback a wp_mail
        $success = $this->send_via_wp_mail($admin_email, $subject, $content);
        update_option('dbst_last_email_method', 'wp_mail');
        
        return $success;
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
     * Enviar email via Mailjet API v3.1
     */
    private function send_via_mailjet($to_email, $subject, $content) {
        $api_key = get_option('dbst_mailjet_api_key');
        $secret_key = get_option('dbst_mailjet_secret_key');
        $from_email = get_option('dbst_mailjet_from_email');
        $from_name = get_option('dbst_mailjet_from_name', get_bloginfo('name'));
        $sandbox_mode = get_option('dbst_mailjet_sandbox_mode', false);
        
        $payload = array(
            'Messages' => array(
                array(
                    'From' => array(
                        'Email' => $from_email,
                        'Name' => $from_name
                    ),
                    'To' => array(
                        array(
                            'Email' => $to_email,
                            'Name' => 'Administrador'
                        )
                    ),
                    'Subject' => $subject,
                    'HTMLPart' => $content,
                    'CustomID' => 'dbst-daily-report-' . time(),
                    'CustomCampaign' => 'DB-SafeTrigger-Reports',
                    'URLTags' => 'utm_source=dbst&utm_medium=email&utm_campaign=daily_report',
                    'Headers' => array(
                        'X-DB-SafeTrigger-Version' => DB_SAFETRIGGER_VERSION,
                        'X-WordPress-Site' => home_url()
                    )
                )
            )
        );
        
        // Activar modo sandbox si está configurado
        if ($sandbox_mode) {
            $payload['SandboxMode'] = true;
        }
        
        $response = wp_remote_post(self::MAILJET_API_URL, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $secret_key),
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($payload),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            update_option('dbst_last_mailjet_error', $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code === 200) {
            $data = json_decode($response_body, true);
            if (isset($data['Messages'][0]['Status']) && $data['Messages'][0]['Status'] === 'success') {
                // Guardar MessageID y MessageUUID para tracking
                $message_info = $data['Messages'][0]['To'][0];
                update_option('dbst_last_message_id', $message_info['MessageID']);
                update_option('dbst_last_message_uuid', $message_info['MessageUUID']);
                delete_option('dbst_last_mailjet_error');
                return true;
            } else {
                // Error en el mensaje específico
                $errors = isset($data['Messages'][0]['Errors']) ? $data['Messages'][0]['Errors'] : array();
                $error_msg = 'Error en validación: ';
                foreach ($errors as $error) {
                    $error_msg .= $error['ErrorMessage'] . ' ';
                }
                update_option('dbst_last_mailjet_error', $error_msg);
                return false;
            }
        }
        
        update_option('dbst_last_mailjet_error', "HTTP $response_code: " . $response_body);
        return false;
    }
    
    /**
     * Enviar email via wp_mail (fallback)
     */
    private function send_via_wp_mail($to_email, $subject, $content) {
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        return wp_mail($to_email, $subject, $content, $headers);
    }
    
    /**
     * Enviar reporte de prueba
     */
    public function send_test_report() {
        // Generar datos de prueba
        $test_data = array(
            'date' => current_time('Y-m-d'),
            'total_events' => 25,
            'unique_tables' => 3,
            'summary' => array(
                'updates' => 20,
                'deletes' => 5,
                'tables_affected' => array('wp_posts', 'wp_users', 'wp_comments')
            ),
            'details' => array(
                (object) array(
                    'table_name' => 'wp_posts',
                    'action' => 'UPDATE',
                    'total_events' => 15,
                    'first_event' => current_time('Y-m-d H:i:s'),
                    'last_event' => current_time('Y-m-d H:i:s')
                ),
                (object) array(
                    'table_name' => 'wp_users',
                    'action' => 'UPDATE',
                    'total_events' => 5,
                    'first_event' => current_time('Y-m-d H:i:s'),
                    'last_event' => current_time('Y-m-d H:i:s')
                ),
                (object) array(
                    'table_name' => 'wp_comments',
                    'action' => 'DELETE',
                    'total_events' => 5,
                    'first_event' => current_time('Y-m-d H:i:s'),
                    'last_event' => current_time('Y-m-d H:i:s')
                )
            )
        );
        
        $content = $this->build_email_content($test_data);
        return $this->send_email($content);
    }
    
    /**
     * Obtener estadísticas de envío
     */
    public function get_email_stats() {
        return array(
            'last_report_sent' => get_option('dbst_last_report_sent'),
            'last_report_success' => get_option('dbst_last_report_success'),
            'last_email_method' => get_option('dbst_last_email_method'),
            'mailjet_configured' => $this->is_mailjet_configured(),
            'last_mailjet_error' => get_option('dbst_last_mailjet_error'),
            'next_scheduled' => wp_next_scheduled(self::DAILY_HOOK)
        );
    }
}