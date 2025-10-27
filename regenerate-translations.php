<?php
/**
 * Script para regenerar archivos .mo de traducciones
 */

// Verificar que estamos en el directorio correcto
if (!file_exists('db-safetrigger.php')) {
    die("Error: Ejecutar desde el directorio raíz del plugin\n");
}

echo "Regenerando archivos .mo de traducciones...\n\n";

$languages = ['en_US', 'es_ES'];

foreach ($languages as $lang) {
    $po_file = "languages/db-safetrigger-{$lang}.po";
    $mo_file = "languages/db-safetrigger-{$lang}.mo";
    
    if (!file_exists($po_file)) {
        echo "❌ No encontrado: $po_file\n";
        continue;
    }
    
    echo "🔄 Procesando: $lang\n";
    echo "   Archivo .po: $po_file\n";
    echo "   Archivo .mo: $mo_file\n";
    
    // Leer archivo .po
    $po_content = file_get_contents($po_file);
    
    if (empty($po_content)) {
        echo "   ❌ Archivo .po vacío\n";
        continue;
    }
    
    // Crear un .mo mínimo funcional
    // Para simplificar, vamos a crear un mapeo básico
    $translations = [];
    
    // Parsear entradas del .po
    $lines = explode("\n", $po_content);
    $current_msgid = '';
    $current_msgstr = '';
    $in_msgid = false;
    $in_msgstr = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (strpos($line, 'msgid ') === 0) {
            // Guardar traducción anterior si existe
            if (!empty($current_msgid) && !empty($current_msgstr)) {
                $translations[$current_msgid] = $current_msgstr;
            }
            
            $current_msgid = trim(substr($line, 6), '"');
            $current_msgstr = '';
            $in_msgid = true;
            $in_msgstr = false;
        } elseif (strpos($line, 'msgstr ') === 0) {
            $current_msgstr = trim(substr($line, 7), '"');
            $in_msgid = false;
            $in_msgstr = true;
        } elseif (strpos($line, '"') === 0 && $in_msgid) {
            $current_msgid .= trim($line, '"');
        } elseif (strpos($line, '"') === 0 && $in_msgstr) {
            $current_msgstr .= trim($line, '"');
        }
    }
    
    // Guardar última traducción
    if (!empty($current_msgid) && !empty($current_msgstr)) {
        $translations[$current_msgid] = $current_msgstr;
    }
    
    echo "   📝 Traducciones encontradas: " . count($translations) . "\n";
    
    // Crear archivo .mo básico
    $mo_data = '';
    
    // Header .mo simple
    $mo_data .= pack('V', 0x950412de); // Magic number
    $mo_data .= pack('V', 0);          // Version
    $mo_data .= pack('V', count($translations)); // Number of strings
    $mo_data .= pack('V', 28);         // Offset of table with original strings
    $mo_data .= pack('V', 28 + count($translations) * 8); // Offset of table with translation strings
    $mo_data .= pack('V', 0);          // Hash table size
    $mo_data .= pack('V', 0);          // Hash table offset
    
    // Construir tablas
    $originals = '';
    $translations_data = '';
    $orig_offsets = [];
    $trans_offsets = [];
    
    $offset = 28 + count($translations) * 16;
    
    foreach ($translations as $orig => $trans) {
        if (empty($orig)) continue;
        
        $orig_offsets[] = pack('V', strlen($orig)) . pack('V', $offset);
        $offset += strlen($orig) + 1;
        $originals .= $orig . "\0";
    }
    
    foreach ($translations as $orig => $trans) {
        if (empty($orig)) continue;
        
        $trans_offsets[] = pack('V', strlen($trans)) . pack('V', $offset);
        $offset += strlen($trans) + 1;
        $translations_data .= $trans . "\0";
    }
    
    $mo_data .= implode('', $orig_offsets);
    $mo_data .= implode('', $trans_offsets);
    $mo_data .= $originals;
    $mo_data .= $translations_data;
    
    // Escribir archivo .mo
    if (file_put_contents($mo_file, $mo_data)) {
        echo "   ✅ Archivo .mo generado: " . filesize($mo_file) . " bytes\n";
    } else {
        echo "   ❌ Error escribiendo archivo .mo\n";
    }
    
    echo "\n";
}

echo "🎉 Regeneración completada!\n";
echo "\nPara aplicar los cambios:\n";
echo "1. Refresca la página de administración de WordPress\n";
echo "2. O desactiva y reactiva el plugin\n";
?>