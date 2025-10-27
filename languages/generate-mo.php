<?php
/**
 * Generador avanzado de archivos .mo desde .po
 */

function create_mo_from_po($po_file, $mo_file) {
    if (!file_exists($po_file)) {
        echo "Error: No se encontró $po_file\n";
        return false;
    }
    
    $po_content = file_get_contents($po_file);
    $entries = array();
    
    // Parsear archivo .po
    preg_match_all('/msgid\s+"([^"]*)"\s*msgstr\s+"([^"]*)"/', $po_content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $msgid = stripslashes($match[1]);
        $msgstr = stripslashes($match[2]);
        
        if (!empty($msgid) && !empty($msgstr)) {
            $entries[$msgid] = $msgstr;
        }
    }
    
    if (empty($entries)) {
        echo "No se encontraron traducciones en $po_file\n";
        return false;
    }
    
    // Crear archivo .mo
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
    
    if (file_put_contents($mo_file, $mo_data) !== false) {
        echo "✅ Compilado exitosamente: $mo_file (" . count($entries) . " traducciones)\n";
        return true;
    } else {
        echo "❌ Error escribiendo: $mo_file\n";
        return false;
    }
}

// Directorio actual
$languages_dir = __DIR__ . '/';

echo "🔧 Generando archivos .mo funcionales...\n\n";

// Compilar inglés
echo "Procesando inglés (en_US)...\n";
create_mo_from_po($languages_dir . 'db-safetrigger-en_US.po', $languages_dir . 'db-safetrigger-en_US.mo');

// Compilar español  
echo "Procesando español (es_ES)...\n";
create_mo_from_po($languages_dir . 'db-safetrigger-es_ES.po', $languages_dir . 'db-safetrigger-es_ES.mo');

echo "\n✅ Proceso completado. Los archivos .mo ahora contienen traducciones reales.\n";
?>