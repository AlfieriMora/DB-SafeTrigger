<?php
/**
 * Script simple para compilar traducciones .po a .mo
 * Ejecutar desde línea de comandos: php compile-translations.php
 */

function parse_po_file($po_file) {
    if (!file_exists($po_file)) {
        echo "Error: Archivo $po_file no encontrado\n";
        return false;
    }
    
    $content = file_get_contents($po_file);
    $entries = array();
    
    // Mejorar el regex para manejar strings multilínea
    $lines = explode("\n", $content);
    $current_msgid = '';
    $current_msgstr = '';
    $in_msgid = false;
    $in_msgstr = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (strpos($line, 'msgid "') === 0) {
            // Nuevo msgid
            if (!empty($current_msgid) && !empty($current_msgstr)) {
                $entries[$current_msgid] = $current_msgstr;
            }
            $current_msgid = substr($line, 7, -1); // Quitar 'msgid "' y '"'
            $current_msgstr = '';
            $in_msgid = true;
            $in_msgstr = false;
        } elseif (strpos($line, 'msgstr "') === 0) {
            $current_msgstr = substr($line, 8, -1); // Quitar 'msgstr "' y '"'
            $in_msgid = false;
            $in_msgstr = true;
        } elseif (strpos($line, '"') === 0 && strlen($line) > 2) {
            // Continuación de string
            $append = substr($line, 1, -1); // Quitar comillas
            if ($in_msgid) {
                $current_msgid .= $append;
            } elseif ($in_msgstr) {
                $current_msgstr .= $append;
            }
        }
    }
    
    // Último entry
    if (!empty($current_msgid) && !empty($current_msgstr)) {
        $entries[$current_msgid] = $current_msgstr;
    }
    
    return $entries;
}

function create_mo_file($entries, $mo_file) {
    $header = pack('V', 0x950412de); // Magic number
    $header .= pack('V', 0);         // Version
    $header .= pack('V', count($entries)); // Number of entries
    $header .= pack('V', 28);        // Offset of key table
    $header .= pack('V', 28 + count($entries) * 8); // Offset of value table
    $header .= pack('V', 0);         // Hash table size
    $header .= pack('V', 0);         // Hash table offset
    
    $keys = '';
    $values = '';
    $key_offsets = array();
    $value_offsets = array();
    
    foreach ($entries as $key => $value) {
        $key_offsets[] = array(strlen($key), strlen($keys));
        $keys .= $key . "\0";
        
        $value_offsets[] = array(strlen($value), strlen($values));
        $values .= $value . "\0";
    }
    
    // Key table
    $key_table = '';
    foreach ($key_offsets as $offset) {
        $key_table .= pack('V', $offset[0]); // Length
        $key_table .= pack('V', 28 + count($entries) * 16 + $offset[1]); // Offset
    }
    
    // Value table
    $value_table = '';
    foreach ($value_offsets as $offset) {
        $value_table .= pack('V', $offset[0]); // Length
        $value_table .= pack('V', 28 + count($entries) * 16 + strlen($keys) + $offset[1]); // Offset
    }
    
    $mo_data = $header . $key_table . $value_table . $keys . $values;
    
    return file_put_contents($mo_file, $mo_data) !== false;
}

echo "Compilando traducciones...\n";

// Compilar inglés
echo "Compilando inglés (en_US)...\n";
$en_entries = parse_po_file('db-safetrigger-en_US.po');
if ($en_entries && count($en_entries) > 0) {
    if (create_mo_file($en_entries, 'db-safetrigger-en_US.mo')) {
        echo "✅ Inglés compilado exitosamente (" . count($en_entries) . " traducciones)\n";
    } else {
        echo "❌ Error compilando inglés\n";
    }
} else {
    echo "❌ No se pudieron parsear las traducciones en inglés\n";
}

// Compilar español
echo "Compilando español (es_ES)...\n";
$es_entries = parse_po_file('db-safetrigger-es_ES.po');
if ($es_entries && count($es_entries) > 0) {
    if (create_mo_file($es_entries, 'db-safetrigger-es_ES.mo')) {
        echo "✅ Español compilado exitosamente (" . count($es_entries) . " traducciones)\n";
    } else {
        echo "❌ Error compilando español\n";
    }
} else {
    echo "❌ No se pudieron parsear las traducciones en español\n";
}

echo "Compilación completada.\n";
?>