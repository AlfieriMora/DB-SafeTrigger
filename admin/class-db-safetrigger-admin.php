<?php
/**
 * Funcionalidad del área administrativa
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DB_SafeTrigger_Admin {
    
    /**
     * Identificador del plugin
     *
     * @var string
     */
    private $plugin_name;
    
    /**
     * Versión del plugin
     *
     * @var string
     */
    private $version;
    
    /**
     * Constructor
     *
     * @param string $plugin_name
     * @param string $version
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Registrar estilos del admin
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            DB_SAFETRIGGER_PLUGIN_URL . 'assets/css/db-safetrigger-admin.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    /**
     * Registrar scripts del admin
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            DB_SAFETRIGGER_PLUGIN_URL . 'assets/js/db-safetrigger-admin.js',
            array('jquery'),
            $this->version,
            false
        );
        
        // Localizar script para AJAX
        wp_localize_script(
            $this->plugin_name,
            'db_safetrigger_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('db_safetrigger_nonce')
            )
        );
    }
    
    /**
     * Agregar menú de administración
     */
    public function add_plugin_admin_menu() {
        add_management_page(
            __('DB-SafeTrigger', 'db-safetrigger'),
            __('DB-SafeTrigger', 'db-safetrigger'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page')
        );
    }
    
    /**
     * Mostrar la página de administración
     */
    public function display_plugin_admin_page() {
        include_once DB_SAFETRIGGER_PLUGIN_DIR . 'admin/partials/db-safetrigger-admin-display.php';
    }
}