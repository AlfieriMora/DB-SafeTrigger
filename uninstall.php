<?php
/**
 * Archivo de desinstalación - Limpieza completa del plugin
 *
 * @package DB_SafeTrigger
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Limpieza completa al desinstalar el plugin
 */
function dbst_uninstall_cleanup() {
    global $wpdb;
    
    // 1. Eliminar todos los triggers relacionados con DB-SafeTrigger
    dbst_remove_all_triggers();
    
    // 2. Eliminar tabla nueva BD_SafeTrigger (CON prefijo de WordPress)
    $new_table = $wpdb->prefix . 'BD_SafeTrigger';
    $wpdb->query("DROP TABLE IF EXISTS `$new_table`");
    
    // 3. Eliminar tabla antigua log_auditoria (SIN prefijo) si existe
    $wpdb->query("DROP TABLE IF EXISTS `log_auditoria`");
    
    // 4. Eliminar todas las opciones del plugin
    dbst_remove_plugin_options();
    
    // 5. Eliminar tareas cron programadas
    wp_clear_scheduled_hook('dbst_daily_audit_report');
    
    // 6. Limpiar cache
    wp_cache_flush();
    
    // 7. NUEVO: Forzar eliminación de archivos antes de que WordPress termine
    dbst_force_file_cleanup();
}

/**
 * Eliminar todos los triggers relacionados con el plugin
 */
function dbst_remove_all_triggers() {
    global $wpdb;
    
    // Obtener todos los triggers de la base de datos
    $triggers = $wpdb->get_results("SHOW TRIGGERS");
    
    if (!empty($triggers)) {
        foreach ($triggers as $trigger) {
            // Eliminar triggers que empiecen con 'trg_' (nuestros triggers)
            if (strpos($trigger->Trigger, 'trg_') === 0) {
                $wpdb->query("DROP TRIGGER IF EXISTS `{$trigger->Trigger}`");
            }
        }
    }
    
    // También eliminar triggers específicos por nombre (por si acaso)
    $specific_triggers = array(
        'trg_posts_au', 'trg_posts_bd',
        'trg_users_au', 'trg_users_bd', 
        'trg_comments_au', 'trg_comments_bd'
    );
    
    foreach ($specific_triggers as $trigger_name) {
        $wpdb->query("DROP TRIGGER IF EXISTS `$trigger_name`");
    }
}

/**
 * Eliminar todas las opciones del plugin
 */
function dbst_remove_plugin_options() {
    // Opciones principales
    delete_option('dbst_json_support');
    delete_option('dbst_last_sql_error');
    delete_option('db_safetrigger_version');
    delete_option('dbst_daily_report_enabled');
    delete_option('dbst_admin_email');
    delete_option('db_safetrigger_enabled');
    delete_option('db_safetrigger_monitor_tables');
    delete_option('dbst_create_triggers_needed');
    delete_option('dbst_monitor_tables');
    
    // Opciones de Mailjet
    delete_option('dbst_mailjet_api_key');
    delete_option('dbst_mailjet_secret_key');
    delete_option('dbst_mailjet_from_email');
    delete_option('dbst_mailjet_from_name');
    delete_option('dbst_mailjet_sandbox_mode');
    delete_option('dbst_report_recipients');
    delete_option('dbst_last_mailjet_error');
    delete_option('dbst_last_message_id');
    delete_option('dbst_last_message_uuid');
    delete_option('dbst_last_report_sent');
    delete_option('dbst_last_report_success');
    delete_option('dbst_last_email_method');
    
    // Opciones de migración
    delete_option('dbst_migration_completed');
    delete_option('dbst_migration_records');
    delete_option('dbst_migration_date');
    delete_option('dbst_migration_error');
    delete_option('dbst_old_table_dropped');
    
    // Opciones de actualización
    delete_option('dbst_upgrade_1_1_0_completed');
    delete_option('dbst_triggers_need_update');
}

/**
 * Forzar limpieza de archivos antes de que WordPress termine
 */
function dbst_force_file_cleanup() {
    $plugin_dir = dirname(__FILE__);
    $plugin_name = basename($plugin_dir);
    
    // Lista de nombres válidos del plugin
    $valid_names = array(
        'db-safetrigger-v4-complete', 
        'db-safetrigger-v3-final', 
        'db-safetrigger-v2', 
        'DB-SafeTrigger', 
        'db-safetrigger'
    );
    
    if (!in_array($plugin_name, $valid_names)) {
        return; // No es nuestra carpeta
    }
    
    // Método 1: Vaciar carpeta pero mantener estructura (evita errores de WordPress)
    dbst_empty_plugin_directory($plugin_dir);
    
    // Método 2: Crear archivo .htaccess para bloquear acceso
    dbst_create_block_htaccess($plugin_dir);
    
    // Método 3: Renombrar archivos principales para desactivarlos
    dbst_disable_main_files($plugin_dir);
    
    // Método 4: Registrar para eliminación posterior
    dbst_schedule_cleanup($plugin_dir);
}

/**
 * Vaciar directorio del plugin manteniendo estructura
 */
function dbst_empty_plugin_directory($dir) {
    if (!is_dir($dir)) return;
    
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $path) {
            if ($path->isFile()) {
                // No eliminar el archivo actual (uninstall.php) hasta el final
                if ($path->getPathname() !== __FILE__) {
                    @unlink($path->getPathname());
                }
            }
        }
    } catch (Exception $e) {
        // Falló el método avanzado, usar método básico
        dbst_basic_file_cleanup($dir);
    }
}

/**
 * Método básico de limpieza de archivos
 */
function dbst_basic_file_cleanup($dir) {
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        if (is_file($file) && $file !== __FILE__) {
            @unlink($file);
        } elseif (is_dir($file)) {
            dbst_recursive_rmdir($file);
        }
    }
}

/**
 * Crear .htaccess para bloquear acceso
 */
function dbst_create_block_htaccess($dir) {
    $htaccess_content = "# DB-SafeTrigger - Plugin desinstalado\n";
    $htaccess_content .= "Order deny,allow\n";
    $htaccess_content .= "Deny from all\n";
    $htaccess_content .= "# Este directorio debe ser eliminado manualmente\n";
    
    @file_put_contents($dir . '/.htaccess', $htaccess_content);
}

/**
 * Renombrar archivos principales
 */
function dbst_disable_main_files($dir) {
    $main_files = array('db-safetrigger.php', 'force-cleanup.php');
    foreach ($main_files as $file) {
        $file_path = $dir . '/' . $file;
        if (file_exists($file_path)) {
            @rename($file_path, $file_path . '.disabled-' . time());
        }
    }
}

/**
 * Programar limpieza posterior
 */
function dbst_schedule_cleanup($dir) {
    // Crear archivo de instrucciones para el administrador
    $instructions = "=== DB-SAFETRIGGER DESINSTALADO ===\n\n";
    $instructions .= "Este plugin ha sido desinstalado correctamente.\n";
    $instructions .= "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    $instructions .= "ESTADO DE LA LIMPIEZA:\n";
    $instructions .= "✅ Tablas de base de datos: ELIMINADAS\n";
    $instructions .= "✅ Triggers de MySQL: ELIMINADOS\n";
    $instructions .= "✅ Configuraciones: ELIMINADAS\n";
    $instructions .= "✅ Tareas programadas: ELIMINADAS\n";
    $instructions .= "⚠️  Archivos del plugin: PENDIENTE\n\n";
    $instructions .= "PARA COMPLETAR LA LIMPIEZA:\n";
    $instructions .= "Elimina esta carpeta manualmente:\n";
    $instructions .= "$dir\n\n";
    $instructions .= "MÉTODOS DE ELIMINACIÓN:\n";
    $instructions .= "- Via FTP/SFTP\n";
    $instructions .= "- Administrador de archivos del hosting\n";
    $instructions .= "- Terminal/SSH: rm -rf \"$dir\"\n\n";
    $instructions .= "El sitio web funcionará normalmente sin esta carpeta.\n";
    
    @file_put_contents($dir . '/ELIMINAR_ESTA_CARPETA.txt', $instructions);
    
    // Guardar ubicación para mostrar en admin si es necesario
    update_option('dbst_cleanup_pending', $dir);
}

/**
 * Función recursiva para eliminar directorios (mejorada)
 */
function dbst_recursive_rmdir($dir) {
    if (!is_dir($dir)) {
        error_log("DB-SafeTrigger: No es un directorio válido: $dir");
        return false;
    }
    
    error_log("DB-SafeTrigger: Eliminando directorio: $dir");
    
    try {
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($path)) {
                // Eliminar subdirectorio recursivamente
                if (!dbst_recursive_rmdir($path)) {
                    error_log("DB-SafeTrigger: Fallo eliminando subdirectorio: $path");
                }
            } else {
                // Intentar eliminar archivo
                if (is_writable($path)) {
                    if (!unlink($path)) {
                        error_log("DB-SafeTrigger: No se pudo eliminar archivo: $path");
                        // Intentar cambiar permisos y volver a intentar
                        @chmod($path, 0777);
                        if (!@unlink($path)) {
                            error_log("DB-SafeTrigger: Fallo definitivo eliminando: $path");
                        }
                    }
                } else {
                    error_log("DB-SafeTrigger: Archivo no escribible: $path");
                    // Intentar cambiar permisos
                    @chmod($path, 0777);
                    if (!@unlink($path)) {
                        error_log("DB-SafeTrigger: No se pudo hacer escribible: $path");
                    }
                }
            }
        }
        
        // Intentar eliminar el directorio principal
        if (is_writable($dir)) {
            $result = rmdir($dir);
            if (!$result) {
                error_log("DB-SafeTrigger: No se pudo eliminar directorio principal: $dir");
                @chmod($dir, 0777);
                $result = @rmdir($dir);
            }
            return $result;
        } else {
            error_log("DB-SafeTrigger: Directorio principal no escribible: $dir");
            @chmod($dir, 0777);
            return @rmdir($dir);
        }
        
    } catch (Exception $e) {
        error_log("DB-SafeTrigger: Excepción durante eliminación: " . $e->getMessage());
        return false;
    }
}

// Ejecutar limpieza
dbst_uninstall_cleanup();