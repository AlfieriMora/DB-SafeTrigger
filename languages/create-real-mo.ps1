# Crear archivo .mo con traducción real para inglés
$translations = @{
    "Estado del Sistema" = "System Status"
    "Gestión de Triggers" = "Trigger Management"  
    "Configuración Mailjet" = "Mailjet Configuration"
    "Reportes" = "Reports"
    "Logs de Auditoría" = "Audit Logs"
    "Tabla de Auditoría" = "Audit Table"
    "Configurada correctamente" = "Configured correctly"
    "No encontrada" = "Not found"
    "Triggers Activos" = "Active Triggers"
    "Sistema funcionando" = "System working"
    "Total de Logs" = "Total Logs"
    "Logs Hoy" = "Logs Today"
}

# Función para crear archivo .mo simple pero funcional
function Create-MOFile {
    param(
        [hashtable]$Translations,
        [string]$FilePath
    )
    
    $entries = $Translations.Count
    $keyData = @()
    $valueData = @()
    $keys = ""
    $values = ""
    
    foreach ($pair in $Translations.GetEnumerator()) {
        $keyData += @{
            Text = $pair.Key + [char]0
            Offset = $keys.Length
        }
        $keys += $pair.Key + [char]0
        
        $valueData += @{
            Text = $pair.Value + [char]0
            Offset = $values.Length
        }
        $values += $pair.Value + [char]0
    }
    
    # Crear archivo binario MO
    $bytes = @()
    
    # Magic number (0x950412de little endian)
    $bytes += 0xde, 0x12, 0x04, 0x95
    
    # Version (0)
    $bytes += 0x00, 0x00, 0x00, 0x00
    
    # Number of entries
    $bytes += [byte]($entries -band 0xFF), [byte](($entries -shr 8) -band 0xFF), 
              [byte](($entries -shr 16) -band 0xFF), [byte](($entries -shr 24) -band 0xFF)
    
    # Offset of key table (28)
    $bytes += 0x1c, 0x00, 0x00, 0x00
    
    # Offset of value table (28 + entries * 8)
    $valueTableOffset = 28 + $entries * 8
    $bytes += [byte]($valueTableOffset -band 0xFF), [byte](($valueTableOffset -shr 8) -band 0xFF),
              [byte](($valueTableOffset -shr 16) -band 0xFF), [byte](($valueTableOffset -shr 24) -band 0xFF)
    
    # Hash table size (0)
    $bytes += 0x00, 0x00, 0x00, 0x00
    
    # Hash table offset (0)  
    $bytes += 0x00, 0x00, 0x00, 0x00
    
    # Key table
    $stringOffset = 28 + $entries * 16
    for ($i = 0; $i -lt $entries; $i++) {
        $keyLength = $keyData[$i].Text.Length - 1  # Sin contar el null terminator
        $keyOffset = $stringOffset + $keyData[$i].Offset
        
        # Length
        $bytes += [byte]($keyLength -band 0xFF), [byte](($keyLength -shr 8) -band 0xFF),
                  [byte](($keyLength -shr 16) -band 0xFF), [byte](($keyLength -shr 24) -band 0xFF)
        
        # Offset
        $bytes += [byte]($keyOffset -band 0xFF), [byte](($keyOffset -shr 8) -band 0xFF),
                  [byte](($keyOffset -shr 16) -band 0xFF), [byte](($keyOffset -shr 24) -band 0xFF)
    }
    
    # Value table
    $valueStringOffset = $stringOffset + $keys.Length
    for ($i = 0; $i -lt $entries; $i++) {
        $valueLength = $valueData[$i].Text.Length - 1  # Sin contar el null terminator
        $valueOffset = $valueStringOffset + $valueData[$i].Offset
        
        # Length
        $bytes += [byte]($valueLength -band 0xFF), [byte](($valueLength -shr 8) -band 0xFF),
                  [byte](($valueLength -shr 16) -band 0xFF), [byte](($valueLength -shr 24) -band 0xFF)
        
        # Offset
        $bytes += [byte]($valueOffset -band 0xFF), [byte](($valueOffset -shr 8) -band 0xFF),
                  [byte](($valueOffset -shr 16) -band 0xFF), [byte](($valueOffset -shr 24) -band 0xFF)
    }
    
    # Strings (keys)
    foreach ($char in $keys.ToCharArray()) {
        $bytes += [byte][char]$char
    }
    
    # Strings (values)
    foreach ($char in $values.ToCharArray()) {
        $bytes += [byte][char]$char
    }
    
    # Escribir archivo
    [System.IO.File]::WriteAllBytes($FilePath, [byte[]]$bytes)
    Write-Host "✅ Creado: $FilePath ($entries traducciones)"
}

# Crear archivo para inglés
Create-MOFile -Translations $translations -FilePath "$PWD\db-safetrigger-en_US.mo"

# Para español (sin cambios en las traducciones)
$translationsES = @{
    "Estado del Sistema" = "Estado del Sistema"
    "Gestión de Triggers" = "Gestión de Triggers"
    "Configuración Mailjet" = "Configuración Mailjet"
    "Reportes" = "Reportes"
    "Logs de Auditoría" = "Logs de Auditoría"
}

Create-MOFile -Translations $translationsES -FilePath "$PWD\db-safetrigger-es_ES.mo"

Write-Host "🎉 Archivos .mo creados con traducciones funcionales"