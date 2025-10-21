<?php
/**
 * Clase DBST_Session - Manejo de variable de sesión para capturar usuario WordPress
 *
 * @package DB_SafeTrigger
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DBST_Session {
    
    /**
     * Inicializar hooks para capturar usuario WordPress
     */
    public static function init() {
        // SUPER AGRESIVO: Establecer en CADA request
        add_action('init', array(__CLASS__, 'aggressive_set_user'), 1);
        add_action('wp_loaded', array(__CLASS__, 'aggressive_set_user'), 1);
        add_action('admin_init', array(__CLASS__, 'aggressive_set_user'), 1);
        add_action('wp_login', array(__CLASS__, 'aggressive_set_user'), 1);
        add_action('set_current_user', array(__CLASS__, 'aggressive_set_user'), 1);
        
        // INTERCEPTAR TODAS las operaciones de WordPress que modifican BD
        add_action('wp_insert_post_data', array(__CLASS__, 'force_set_before_operation'), 1, 2);
        add_action('wp_update_post', array(__CLASS__, 'force_set_before_operation'), 1);
        add_action('pre_post_update', array(__CLASS__, 'force_set_before_operation'), 1);
        add_action('before_delete_post', array(__CLASS__, 'force_set_before_operation'), 1);
        add_action('wp_insert_post', array(__CLASS__, 'force_set_before_operation'), 1);
        
        // Usuario operations
        add_action('profile_update', array(__CLASS__, 'force_set_before_operation'), 1);
        add_action('user_register', array(__CLASS__, 'force_set_before_operation'), 1);
        add_action('delete_user', array(__CLASS__, 'force_set_before_operation'), 1);
        
        // Comment operations
        add_action('wp_insert_comment', array(__CLASS__, 'force_set_before_operation'), 1);
        add_action('wp_update_comment_count', array(__CLASS__, 'force_set_before_operation'), 1);
        add_action('delete_comment', array(__CLASS__, 'force_set_before_operation'), 1);
        
        // AJAX y REST API
        add_action('wp_ajax_*', array(__CLASS__, 'aggressive_set_user'), 1);
        add_action('wp_ajax_nopriv_*', array(__CLASS__, 'aggressive_set_user'), 1);
        add_action('rest_api_init', array(__CLASS__, 'aggressive_set_user'), 1);
        
        // Hook UNIVERSAL antes de cualquier consulta de DB
        add_filter('query', array(__CLASS__, 'before_db_query'), 1);
        
        // MÉTODO DEFINITIVO: Interceptar WPDB directamente
        self::hook_wpdb_directly();
        
        // Limpiar al final
        add_action('shutdown', array(__CLASS__, 'cleanup_session_var'), 999);
    }
    
    /**
     * Interceptar consultas de base de datos para asegurar variable de sesión
     */
    public static function before_db_query($query) {
        // Solo interceptar UPDATE/DELETE en tablas monitoreadas
        if (preg_match('/^(UPDATE|DELETE)\s+.*\b(posts|users|comments)\b/i', $query)) {
            $user_id = get_current_user_id();
            if ($user_id) {
                global $wpdb;
                $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $user_id));
            }
        }
        return $query;
    }
    
    /**
     * Forzar establecimiento de usuario antes de operaciones críticas
     */
    public static function force_set_user($post_id = null) {
        self::set_current_wp_user();
    }
    
    /**
     * Establecer variable de sesión de forma SUPER AGRESIVA
     */
    public static function aggressive_set_user() {
        global $wpdb;
        
        $user_id = get_current_user_id();
        
        if ($user_id && !wp_doing_cron()) {
            // FORZAR establecimiento MÚLTIPLES VECES
            $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $user_id));
            $wpdb->query($wpdb->prepare("SET @wp_user = %d", $user_id)); // Variable alternativa
            
            // Verificar inmediatamente
            $verify = $wpdb->get_var("SELECT @wp_current_user_id");
            
            // Si no se estableció, intentar de nuevo con sintaxis diferente
            if (!$verify || $verify != $user_id) {
                $wpdb->query("SET @wp_current_user_id = " . intval($user_id));
            }
            
            // Log para debugging
            error_log("DBST: Usuario establecido - ID: $user_id, Verificación: " . ($verify ?: 'NULL'));
        } else {
            $wpdb->query("SET @wp_current_user_id = NULL");
        }
    }
    
    /**
     * Forzar establecimiento antes de CUALQUIER operación
     */
    public static function force_set_before_operation($data = null) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        
        if ($user_id) {
            // TRIPLE establecimiento para asegurar
            $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $user_id));
            $wpdb->query("SET @wp_current_user_id = " . intval($user_id));
            
            // Log crítico
            error_log("DBST: FORZANDO usuario antes de operación - ID: $user_id");
        }
        
        return $data; // Importante: devolver los datos sin modificar
    }
    
    /**
     * Establecer variable de sesión MySQL con el ID del usuario WordPress actual
     */
    public static function set_current_wp_user() {
        // Llamar al método agresivo
        self::aggressive_set_user();
    }
    
    /**
     * Obtener ID de usuario en contexto AJAX
     */
    private static function get_ajax_user_id() {
        // Verificar si existe usuario en la sesión actual
        if (function_exists('wp_get_current_user')) {
            $user = wp_get_current_user();
            return $user->exists() ? $user->ID : 0;
        }
        
        return 0;
    }
    
    /**
     * Establecer usuario específico para operaciones administrativas
     */
    public static function set_admin_user($user_id) {
        global $wpdb;
        
        if (is_numeric($user_id) && $user_id > 0) {
            $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $user_id));
        }
    }
    
    /**
     * Limpiar variable de sesión
     */
    public static function cleanup_session_var() {
        global $wpdb;
        
        $wpdb->query("SET @wp_current_user_id = NULL");
    }
    
    /**
     * Obtener usuario WordPress actual desde variable de sesión
     */
    public static function get_current_wp_user_id() {
        global $wpdb;
        
        $user_id = $wpdb->get_var("SELECT @wp_current_user_id");
        return $user_id ? intval($user_id) : null;
    }
    
    /**
     * Hook para operaciones de posts
     */
    public static function hook_post_operations() {
        add_action('pre_post_update', array(__CLASS__, 'set_current_wp_user'), 1);
        add_action('before_delete_post', array(__CLASS__, 'set_current_wp_user'), 1);
        add_action('wp_insert_post', array(__CLASS__, 'set_current_wp_user'), 1);
    }
    
    /**
     * Hook para operaciones de usuarios
     */
    public static function hook_user_operations() {
        add_action('profile_update', array(__CLASS__, 'set_current_wp_user'), 1);
        add_action('user_register', array(__CLASS__, 'set_current_wp_user'), 1);
        add_action('delete_user', array(__CLASS__, 'set_current_wp_user'), 1);
    }
    
    /**
     * Hook para operaciones de comentarios
     */
    public static function hook_comment_operations() {
        add_action('wp_insert_comment', array(__CLASS__, 'set_current_wp_user'), 1);
        add_action('wp_update_comment_count', array(__CLASS__, 'set_current_wp_user'), 1);
        add_action('delete_comment', array(__CLASS__, 'set_current_wp_user'), 1);
    }
    
    /**
     * Hook directo a WPDB para interceptar TODAS las consultas
     */
    public static function hook_wpdb_directly() {
        global $wpdb;
        
        // Agregar callback antes de cada consulta
        if (method_exists($wpdb, 'add_callback')) {
            $wpdb->add_callback('before_query', array(__CLASS__, 'wpdb_before_query_callback'));
        }
        
        // Método alternativo: Override del método query de WPDB
        add_filter('query', array(__CLASS__, 'intercept_wpdb_query'), 1);
        
        // Hook específico para wp_update_post
        add_action('wp_update_post', function($post_id) {
            global $wpdb;
            $user_id = get_current_user_id();
            if ($user_id) {
                $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $user_id));
                error_log("DBST: Variable establecida en wp_update_post - Usuario: $user_id");
            }
        }, 1);
        
        // Hook específico para wp_delete_post
        add_action('wp_delete_post', function($post_id) {
            global $wpdb;
            $user_id = get_current_user_id();
            if ($user_id) {
                $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $user_id));
                error_log("DBST: Variable establecida en wp_delete_post - Usuario: $user_id");
            }
        }, 1);
    }
    
    /**
     * Callback que se ejecuta antes de cada consulta WPDB
     */
    public static function wpdb_before_query_callback($query) {
        // Solo interceptar UPDATE/DELETE en tablas monitoreadas
        if (preg_match('/^(UPDATE|DELETE)\s+.*\b(posts|users|comments)\b/i', $query)) {
            global $wpdb;
            $user_id = get_current_user_id();
            
            if ($user_id) {
                $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $user_id));
                error_log("DBST: Variable establecida via callback - Query: " . substr($query, 0, 50) . "... - Usuario: $user_id");
            }
        }
    }
    
    /**
     * Interceptar todas las consultas WPDB
     */
    public static function intercept_wpdb_query($query) {
        // Solo interceptar UPDATE/DELETE en tablas monitoreadas
        if (preg_match('/^(UPDATE|DELETE)\s+.*\b(posts|users|comments)\b/i', $query)) {
            global $wpdb;
            $user_id = get_current_user_id();
            
            if ($user_id) {
                // ESTABLECER VARIABLE JUSTO ANTES DE LA CONSULTA
                $wpdb->query($wpdb->prepare("SET @wp_current_user_id = %d", $user_id));
                
                // Log para debugging
                error_log("DBST: INTERCEPTADO - Query: " . substr($query, 0, 100) . "... - Usuario: $user_id");
            }
        }
        
        return $query;
    }
}