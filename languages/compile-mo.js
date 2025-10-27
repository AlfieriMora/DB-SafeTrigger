const fs = require('fs');
const path = require('path');

function parsePO(content) {
    const entries = {};
    const lines = content.split('\n');
    let currentMsgid = '';
    let currentMsgstr = '';
    let inMsgid = false;
    let inMsgstr = false;
    
    for (const line of lines) {
        const trimmed = line.trim();
        
        if (trimmed.startsWith('msgid "')) {
            // Guardar entrada anterior si existe
            if (currentMsgid && currentMsgstr) {
                entries[currentMsgid] = currentMsgstr;
            }
            currentMsgid = trimmed.slice(7, -1); // Quitar 'msgid "' y '"'
            currentMsgstr = '';
            inMsgid = true;
            inMsgstr = false;
        } else if (trimmed.startsWith('msgstr "')) {
            currentMsgstr = trimmed.slice(8, -1); // Quitar 'msgstr "' y '"'
            inMsgid = false;
            inMsgstr = true;
        } else if (trimmed.startsWith('"') && trimmed.endsWith('"')) {
            // Continuación de string
            const append = trimmed.slice(1, -1);
            if (inMsgid) {
                currentMsgid += append;
            } else if (inMsgstr) {
                currentMsgstr += append;
            }
        }
    }
    
    // Última entrada
    if (currentMsgid && currentMsgstr) {
        entries[currentMsgid] = currentMsgstr;
    }
    
    return entries;
}

function createMO(entries) {
    const keys = Object.keys(entries);
    const count = keys.length;
    
    // Crear buffer para el archivo .mo
    let keyTable = Buffer.alloc(0);
    let valueTable = Buffer.alloc(0);
    let keyData = Buffer.alloc(0);
    let valueData = Buffer.alloc(0);
    
    keys.forEach(key => {
        const value = entries[key];
        const keyBuffer = Buffer.from(key + '\0', 'utf8');
        const valueBuffer = Buffer.from(value + '\0', 'utf8');
        
        // Tabla de keys (length, offset)
        const keyTableEntry = Buffer.alloc(8);
        keyTableEntry.writeUInt32LE(keyBuffer.length - 1, 0); // sin \0
        keyTableEntry.writeUInt32LE(28 + count * 16 + keyData.length, 4);
        keyTable = Buffer.concat([keyTable, keyTableEntry]);
        
        // Tabla de values (length, offset)
        const valueTableEntry = Buffer.alloc(8);
        valueTableEntry.writeUInt32LE(valueBuffer.length - 1, 0); // sin \0
        valueTableEntry.writeUInt32LE(28 + count * 16 + keyData.length + keyBuffer.length + valueData.length, 4);
        valueTable = Buffer.concat([valueTable, valueTableEntry]);
        
        keyData = Buffer.concat([keyData, keyBuffer]);
        valueData = Buffer.concat([valueData, valueBuffer]);
    });
    
    // Header
    const header = Buffer.alloc(28);
    header.writeUInt32LE(0x950412de, 0);  // Magic number
    header.writeUInt32LE(0, 4);           // Version
    header.writeUInt32LE(count, 8);       // Number of strings
    header.writeUInt32LE(28, 12);         // Offset of key table
    header.writeUInt32LE(28 + count * 8, 16); // Offset of value table
    header.writeUInt32LE(0, 20);          // Hash table size
    header.writeUInt32LE(0, 24);          // Hash table offset
    
    return Buffer.concat([header, keyTable, valueTable, keyData, valueData]);
}

function compilePOtoMO(poFile, moFile) {
    if (!fs.existsSync(poFile)) {
        console.log(`❌ Archivo no encontrado: ${poFile}`);
        return false;
    }
    
    const content = fs.readFileSync(poFile, 'utf8');
    const entries = parsePO(content);
    
    if (Object.keys(entries).length === 0) {
        console.log(`❌ No se encontraron traducciones en: ${poFile}`);
        return false;
    }
    
    const moData = createMO(entries);
    
    try {
        fs.writeFileSync(moFile, moData);
        console.log(`✅ ${moFile} creado exitosamente (${Object.keys(entries).length} traducciones)`);
        return true;
    } catch (error) {
        console.log(`❌ Error escribiendo ${moFile}: ${error.message}`);
        return false;
    }
}

console.log('🔧 Compilando archivos .po a .mo...\n');

// Compilar inglés
compilePOtoMO('db-safetrigger-en_US.po', 'db-safetrigger-en_US.mo');

// Compilar español
compilePOtoMO('db-safetrigger-es_ES.po', 'db-safetrigger-es_ES.mo');

console.log('\n✅ Compilación completada.');