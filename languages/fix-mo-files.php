<?php
/**
 * Script para reparar archivos .mo corruptos
 * Uso: php fix-mo-files.php
 */

function parse_po_simple($po_file) {
    if (!file_exists($po_file)) {
        return false;
    }
    
    $content = file_get_contents($po_file);
    $entries = array();
    
    // Usar regex simple para extraer pares msgid/msgstr
    preg_match_all('/msgid\s+"([^"]*)"[\s\n]*msgstr\s+"([^"]*)"/', $content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $msgid = $match[1];
        $msgstr = $match[2];
        
        if (!empty($msgid) && !empty($msgstr)) {
            $entries[$msgid] = $msgstr;
        }
    }
    
    return $entries;
}

function create_simple_mo($entries, $mo_file) {
    // Crear un archivo .mo válido pero simple
    $count = count($entries);
    
    // Magic number (0x950412de para little-endian .mo)
    $mo = pack('V', 0x950412de);
    $mo .= pack('V', 0);              // Versión
    $mo .= pack('V', $count);         // Número de strings
    $mo .= pack('V', 28);             // Offset tabla keys
    $mo .= pack('V', 28 + $count * 8); // Offset tabla values
    $mo .= pack('V', 0);              // Hash table size
    $mo .= pack('V', 0);              // Hash table offset
    
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
    
    // Tabla de keys
    foreach ($key_offsets as $offset) {
        $mo .= pack('V', $offset[0]); // Length
        $mo .= pack('V', 28 + $count * 16 + $offset[1]); // Offset
    }
    
    // Tabla de values
    foreach ($value_offsets as $offset) {
        $mo .= pack('V', $offset[0]); // Length
        $mo .= pack('V', 28 + $count * 16 + strlen($keys) + $offset[1]); // Offset
    }
    
    // Keys y values
    $mo .= $keys;
    $mo .= $values;
    
    return file_put_contents($mo_file, $mo) !== false;
}

echo "Reparando archivos .mo...\n";

// Procesar inglés
$en_entries = parse_po_simple('db-safetrigger-en_US.po');
if ($en_entries && count($en_entries) > 0) {
    if (create_simple_mo($en_entries, 'db-safetrigger-en_US.mo')) {
        echo "✅ db-safetrigger-en_US.mo creado (" . count($en_entries) . " traducciones)\n";
    } else {
        echo "❌ Error creando db-safetrigger-en_US.mo\n";
    }
} else {
    echo "❌ No se pudieron extraer traducciones de en_US.po\n";
}

// Procesar español
$es_entries = parse_po_simple('db-safetrigger-es_ES.po');
if ($es_entries && count($es_entries) > 0) {
    if (create_simple_mo($es_entries, 'db-safetrigger-es_ES.mo')) {
        echo "✅ db-safetrigger-es_ES.mo creado (" . count($es_entries) . " traducciones)\n";
    } else {
        echo "❌ Error creando db-safetrigger-es_ES.mo\n";
    }
} else {
    echo "❌ No se pudieron extraer traducciones de es_ES.po\n";
}

echo "Proceso completado.\n";
?>