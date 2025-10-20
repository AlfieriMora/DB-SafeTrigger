<?php
/**
 * Clase principal del plugin DB-SafeTrigger
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DB_SafeTrigger {
    
    /**
     * Versión del plugin
     *
     * @var string
     */
    protected $version;
    
    /**
     * Identificador único del plugin
     *
     * @var string
     */
    protected $plugin_name;
    
    /**
     * Instancia del loader para hooks
     *
     * @var DB_SafeTrigger_Loader
     */
    protected $loader;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->version = DB_SAFETRIGGER_VERSION;
        $this->plugin_name = 'db-safetrigger';
        
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Cargar las dependencias necesarias
     */
    private function load_dependencies() {
        require_once DB_SAFETRIGGER_PLUGIN_DIR . 'includes/class-db-safetrigger-loader.php';
        require_once DB_SAFETRIGGER_PLUGIN_DIR . 'includes/class-db-safetrigger-i18n.php';
        require_once DB_SAFETRIGGER_PLUGIN_DIR . 'admin/class-db-safetrigger-admin.php';
        
        $this->loader = new DB_SafeTrigger_Loader();
    }
    
    /**
     * Definir la configuración de idioma
     */
    private function set_locale() {
        $plugin_i18n = new DB_SafeTrigger_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }
    
    /**
     * Registrar hooks para el área administrativa
     */
    private function define_admin_hooks() {
        $plugin_admin = new DB_SafeTrigger_Admin($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
    }
    
    /**
     * Registrar hooks para el área pública
     */
    private function define_public_hooks() {
        // Hooks para el frontend si son necesarios
    }
    
    /**
     * Ejecutar el plugin
     */
    public function run() {
        $this->loader->run();
    }
    
    /**
     * Obtener el nombre del plugin
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    /**
     * Obtener la versión del plugin
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }
    
    /**
     * Obtener el loader
     *
     * @return DB_SafeTrigger_Loader
     */
    public function get_loader() {
        return $this->loader;
    }
}