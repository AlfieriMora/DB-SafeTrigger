<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="notice notice-info">
        <p><strong>Nota:</strong> Esta página de administración ha sido reemplazada por la nueva interfaz mejorada.</p>
        <p>Para acceder a la configuración completa de DB-SafeTrigger, vaya a <strong>Ajustes → DB-SafeTrigger</strong></p>
    </div>
    
    <div class="card">
        <h2>Redirección Automática</h2>
        <p>Será redirigido automáticamente a la nueva página de configuración...</p>
        
        <script>
        setTimeout(function() {
            window.location.href = '<?php echo admin_url('options-general.php?page=db-safetrigger'); ?>';
        }, 3000);
        </script>
        
        <p><a href="<?php echo admin_url('options-general.php?page=db-safetrigger'); ?>" class="button button-primary">Ir Ahora</a></p>
    </div>
</div>