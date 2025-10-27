# Instrucciones para Compilar Archivos de Idioma

Este plugin incluye soporte completo para internacionalización (i18n). Para que las traducciones funcionen correctamente, necesitas compilar los archivos `.po` a `.mo`.

## Archivos Incluidos

- `languages/db-safetrigger.pot` - Archivo plantilla con todas las cadenas de texto
- `languages/db-safetrigger-es_ES.po` - Traducción al español
- `languages/db-safetrigger-en_US.po` - Traducción al inglés

## Métodos para Compilar

### Opción 1: Usar msgfmt (Recomendado)

Si tienes `gettext` instalado en tu sistema:

```bash
# Para español
msgfmt languages/db-safetrigger-es_ES.po -o languages/db-safetrigger-es_ES.mo

# Para inglés  
msgfmt languages/db-safetrigger-en_US.po -o languages/db-safetrigger-en_US.mo
```

### Opción 2: Usar Poedit

1. Descarga e instala [Poedit](https://poedit.net/)
2. Abre cada archivo `.po` en Poedit
3. Ve a File > Compile to MO...
4. Guarda el archivo `.mo` en la misma carpeta

### Opción 3: Script PHP incluido

Si tienes PHP en línea de comandos:

```bash
php compile-mo.php
```

### Opción 4: Plugin de WordPress

Instala un plugin como "Loco Translate" que puede compilar los archivos automáticamente.

## Verificar que Funciona

1. Ve a WordPress Admin > Configuración > General
2. Cambia el idioma del sitio
3. Ve a DB-SafeTrigger en el menú de administración
4. Verifica que el texto aparezca en el idioma seleccionado

## Agregar Nuevos Idiomas

1. Copia `db-safetrigger.pot` a `db-safetrigger-[codigo_idioma].po`
2. Traduce las cadenas de texto
3. Compila a `.mo` usando uno de los métodos anteriores

## Notas Importantes

- Los archivos `.mo` son binarios y deben estar presentes para que las traducciones funcionen
- El plugin detecta automáticamente el idioma configurado en WordPress
- Si no encuentra el archivo de idioma correspondiente, usará el texto por defecto (español)