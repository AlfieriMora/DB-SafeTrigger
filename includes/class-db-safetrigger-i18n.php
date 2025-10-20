<?php
/**
 * Manejo de internacionalización
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DB_SafeTrigger_i18n {
    
    /**
     * Cargar el dominio de texto del plugin
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'db-safetrigger',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}