# �️ DB-SafeTrigger v1.1.0

Plugin profesional de **Trazabilidad y Auditoría a Nivel de Base de Datos** para WordPress con integración avanzada de **Mailjet Send API v3.1** y sistema de reportes automáticos.

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](LICENSE)
[![Mailjet](https://img.shields.io/badge/Mailjet-Send%20API%20v3.1-orange.svg)](https://www.mailjet.com/)
[![Version](https://img.shields.io/badge/Version-1.1.0-brightgreen.svg)](https://github.com/AlfieriMora/DB-SafeTrigger)

## 📋 Descripción

**DB-SafeTrigger** es un sistema empresarial de auditoría y trazabilidad que monitorea automáticamente todos los cambios críticos en la base de datos de WordPress mediante **triggers MySQL optimizados** y envía **reportes profesionales en tiempo real** usando la API avanzada de Mailjet. Ideal para sitios que requieren cumplimiento normativo, seguridad empresarial y trazabilidad completa de datos.

### ✨ Características Principales

#### 🔍 **Sistema de Auditoría Avanzado**
- **Triggers MySQL automáticos** para detectar cambios en tiempo real (UPDATE/DELETE)
- **Captura inteligente del usuario WordPress** mediante sistema de sesiones MySQL
- **Tabla de auditoría optimizada** `{prefijo_wp}BD_SafeTrigger` con índices de rendimiento
- **Migración automática** desde versiones anteriores con preservación de datos
- **Monitoreo integral** de tablas críticas: posts, users, comments

#### 📧 **Integración Mailjet Send API v3.1 Completa**
- **Send API v3.1** con todas las características empresariales de Mailjet
- **Emails HTML responsivos** con diseño profesional y estadísticas integradas
- **Reportes automáticos programables** (diario, semanal, mensual) 
- **Reportes instantáneos** bajo demanda con datos en tiempo real
- **Múltiples destinatarios** con gestión avanzada de listas
- **Tracking completo** con CustomID, CustomCampaign y URLTags

#### 🎛️ **Panel de Administración Profesional (5 Pestañas)**
- **📊 Estado del Sistema**: Dashboard con métricas en tiempo real y estado de componentes
- **� Gestión de Triggers**: Creación/eliminación automática con validación de estado
- **📧 Configuración Mailjet**: Setup completo de credenciales y configuraciones avanzadas
- **📋 Reportes**: Sistema de reportes con configuración granular y envío programado
- **📜 Logs de Auditoría**: Visor avanzado con filtros, paginación y búsqueda por usuario

#### ⚡ **Características Técnicas Avanzadas**
- **Detección automática de usuario WordPress** en todas las operaciones (CRUD)
- **Hooks WordPress integrados** para interceptar cambios antes de que ocurran
- **Sistema de sesiones MySQL** para capturar contexto de usuario en triggers
- **Manejo robusto de errores** con logging detallado y recuperación automática
- **Optimización de rendimiento** con índices de base de datos específicos
- **Compatibilidad total** con hosting compartido y VPS empresarial

## 🚀 Instalación y Configuración

### 📦 Instalación

#### Método 1: Instalación desde WordPress Admin (Recomendado)
1. Descarga el archivo `DB-SafeTrigger-v1.1.0-final.zip`
2. Ve a **Plugins → Añadir nuevo → Subir plugin**
3. Selecciona el archivo ZIP y haz clic en **Instalar ahora**
4. **Activa** el plugin desde la lista de plugins
5. Ve a **Ajustes → DB-SafeTrigger** para comenzar la configuración

#### Método 2: Instalación Manual vía FTP
1. Extrae los archivos del plugin del ZIP
2. Sube la carpeta `DB-SafeTrigger` a `/wp-content/plugins/`
3. Asegúrate de que los permisos sean correctos (755 para carpetas, 644 para archivos)
4. Activa el plugin desde **Plugins → Plugins instalados**

### ⚙️ Configuración Paso a Paso

#### 1️⃣ **Verificación Initial del Sistema**
Después de activar el plugin, ve a **Ajustes → DB-SafeTrigger**:

1. **📊 Estado del Sistema**: Verifica que la tabla de auditoría se haya creado correctamente
2. Si ves algún error, revisa los logs de PHP y permisos de base de datos
3. Confirma que tu hosting soporta triggers MySQL (la mayoría sí lo hace)

#### 2️⃣ **Configuración de Triggers de Auditoría**
1. Ve a la pestaña **� Gestión de Triggers**
2. Haz clic en **🚀 Crear/Actualizar Triggers**
3. El sistema creará automáticamente 6 triggers (2 por cada tabla: posts, users, comments)
4. Verifica en el panel que muestre "✅ Activo" para todas las tablas monitoreadas
5. Si aparecen errores, revisa que tu usuario de MySQL tenga privilegios TRIGGER

#### 3️⃣ **Configuración de Mailjet (Obligatoria para Reportes)**
Para obtener tus credenciales de Mailjet:

1. **Regístrate en [Mailjet](https://www.mailjet.com/)** (cuenta gratuita disponible)
2. Ve a **Account Settings → API Keys** en tu panel de Mailjet
3. Copia tu **API Key** y **Secret Key**
4. En WordPress, ve a **📧 Configuración Mailjet** y completa:
   - **API Key**: Tu clave pública de Mailjet
   - **Secret Key**: Tu clave secreta de Mailjet
   - **Email Remitente**: Email verificado en Mailjet (ej: `reportes@tudominio.com`)
   - **Nombre del Remitente**: Nombre que aparecerá en los emails (ej: "DB-SafeTrigger")
   - **Destinatarios**: Lista de emails que recibirán los reportes (uno por línea)

#### 4️⃣ **Configuración de Reportes Automáticos**
1. Ve a la pestaña **📋 Reportes**
2. Configura las opciones según tus necesidades:
   - **Reportes automáticos**: Activar/desactivar envío programado
   - **Frecuencia**: Diario, semanal o mensual
   - **Hora de envío**: Hora específica para enviar (formato 24h)
   - **Contenido**: Incluir resumen estadístico y/o detalles de eventos
3. Haz clic en **� Guardar Configuración**
4. **Prueba el sistema** haciendo clic en **📤 Enviar Reporte Ahora**

## 📊 Panel de Administración Detallado

El plugin incluye un **panel de administración profesional de 5 pestañas** accesible desde **Ajustes → DB-SafeTrigger**:

### 📊 **Estado del Sistema**
**Dashboard en tiempo real** con métricas y estado de componentes:
- **Estado de la tabla de auditoría**: Verifica existencia y estructura
- **Contadores en tiempo real**: Total de logs, eventos de hoy, eventos de la semana
- **Estado de triggers**: Lista completa de triggers activos con detalles técnicos
- **Información de sistema**: Versión del plugin, configuración de base de datos
- **Indicadores visuales**: Códigos de color para identificar problemas rápidamente

### 🔧 **Gestión de Triggers**
**Control completo del sistema de auditoría**:
- **Panel de control**: Creación/eliminación masiva de triggers con confirmación
- **Estado de monitoreo por tabla**: Grid visual del estado de cada tabla (posts, users, comments)
- **Tabla de triggers configurados**: Lista detallada con nombre, tabla, evento, timing y estado
- **Estadísticas por tabla**: Conteo de triggers y eventos del día por tabla
- **Información técnica**: Detalles de configuración y troubleshooting

### 📧 **Configuración Mailjet**
**Setup completo de la integración de Mailjet Send API v3.1**:
- **Formulario de credenciales**: API Key, Secret Key con validación en tiempo real
- **Configuración de remitente**: Email y nombre del remitente con verificación
- **Gestión de destinatarios**: Lista de emails con validación y contador
- **Panel de estado**: Indicadores visuales del estado de cada configuración
- **Pruebas de conexión**: Verificación automática de conectividad con Mailjet

### 📋 **Reportes**
**Sistema avanzado de reportes con configuración granular**:
- **Estadísticas en tiempo real**: Eventos del día con desglose por tipo
- **Reportes instantáneos**: Envío inmediato con datos actuales
- **Configuración automática**: Frecuencia, hora, destinatarios y contenido personalizable
- **Vista previa**: Previsualización del reporte antes del envío
- **Estado del sistema**: Próximo envío, último envío, estado de configuración
- **Botones de acción**: Envío inmediato y vista previa con modal interactivo

### 📜 **Logs de Auditoría**
**Visor avanzado de logs con funcionalidades empresariales**:
- **Filtros avanzados**: Por usuario WordPress, tipo de acción (UPDATE/DELETE), tabla específica
- **Paginación inteligente**: Navegación eficiente con 20 registros por página
- **Información detallada**: Fecha/hora, usuario, tabla, acción, ID del registro, usuario de BD
- **Indicadores visuales**: Códigos de color para diferentes tipos de eventos y fechas
- **Vista tabular responsive**: Diseño optimizado para diferentes tamaños de pantalla
- **Contadores**: Total de registros, página actual, filtros aplicados

## 🔧 Características Técnicas Avanzadas

### 🔒 **Sistema de Auditoría de Base de Datos**
- **Triggers MySQL optimizados** para UPDATE y DELETE en tiempo real
- **Tabla de auditoría** `{prefijo_wp}BD_SafeTrigger` con índices de rendimiento:
  - `idx_table_time`: Optimización para consultas por tabla y fecha
  - `idx_action_time`: Optimización para filtros por tipo de acción
  - `idx_wp_user_time`: Optimización para consultas por usuario WordPress
- **Captura automática de contexto**: Usuario WordPress, timestamp, datos antiguos, host cliente
- **Migración inteligente**: Actualización automática desde versiones anteriores preservando datos
- **Compatibilidad amplia**: Funciona en hosting compartido, VPS y servidores dedicados

### 📧 **Integración Mailjet Send API v3.1 Empresarial**
- **Send API v3.1 completa** con todas las características profesionales de Mailjet
- **Emails HTML responsivos** con diseño corporativo y estadísticas integradas
- **Tracking avanzado**: CustomID, CustomCampaign, URLTags para seguimiento detallado
- **Manejo robusto de errores** con logging detallado y reintentos automáticos
- **Múltiples destinatarios** con validación de email y gestión de listas
- **Headers personalizados** para clasificación y filtrado automático

### ⚡ **Sistema de Detección de Usuario WordPress**
- **Clase DBST_Session avanzada** para capturar el contexto de usuario en todas las operaciones
- **Hooks WordPress integrados** en más de 15 puntos de interceptación:
  - Operaciones de posts: `pre_post_update`, `wp_delete_post`, `wp_insert_post`
  - Operaciones de usuarios: `profile_update`, `user_register`, `delete_user`
  - Operaciones de comentarios: `wp_insert_comment`, `wp_update_comment_count`, `delete_comment`
  - AJAX y REST API: Interceptación completa de operaciones asíncronas
- **Variables de sesión MySQL** para mantener contexto entre WordPress y triggers
- **Interceptación de consultas WPDB** para asegurar la captura de usuario en cualquier escenario

### 📋 **Sistema de Reportes Automáticos**
- **Cron jobs WordPress** para envío programado (diario, semanal, mensual)
- **Generación dinámica de contenido** con estadísticas detalladas y eventos recientes
- **Configuración granular** de contenido: resumen estadístico, detalles de eventos, actividad por tabla
- **Vista previa interactiva** con modal JavaScript para previsualización antes del envío
- **Reportes instantáneos** bajo demanda con datos en tiempo real

## 🔧 Requisitos del Sistema

### ⚙️ **Requisitos Mínimos**
- **WordPress**: 5.0 o superior
- **PHP**: 7.4 o superior (recomendado 8.0+)
- **MySQL**: 5.7 o superior / **MariaDB**: 10.2 o superior
- **Memoria PHP**: 128 MB mínimo (recomendado 256 MB+)
- **Permisos de usuario MySQL**: SELECT, INSERT, UPDATE, DELETE
- **Privilegios TRIGGER**: Recomendado (el plugin funciona en la mayoría de hostings sin ellos)

### 🌐 **Compatibilidad de Hosting**
- ✅ **Hosting Compartido**: Compatible con la mayoría de proveedores
- ✅ **VPS/Cloud**: Totalmente compatible
- ✅ **Servidores Dedicados**: Totalmente compatible
- ✅ **WordPress.com Business**: Compatible con limitaciones de plugins
- ⚠️ **Hosting Gratuito**: Puede tener limitaciones en triggers MySQL

### 📡 **Conectividad Requerida**
- **Conexión HTTPS** para comunicación con Mailjet API
- **Puerto 443** abierto para conexiones salientes
- **cURL** habilitado en PHP (estándar en la mayoría de hostings)
- **API externa**: Acceso a `api.mailjet.com` (no bloqueado por firewall)

### 🔐 **Permisos y Seguridad**
- **WordPress**: Usuario con permisos `manage_options`
- **Base de datos**: Usuario con permisos para crear/eliminar triggers
- **Archivos**: Permisos estándar de WordPress (755 para carpetas, 644 para archivos)
- **API Keys**: Almacenamiento seguro en opciones de WordPress

## �️ Estructura de Datos y Monitoreo

### 📋 **Tablas Monitoreadas**
El plugin monitorea automáticamente las siguientes tablas críticas de WordPress:

| Tabla | Descripción | Acciones Monitoreadas | Triggers Creados |
|-------|-------------|----------------------|------------------|
| `{prefijo}_posts` | Entradas, páginas, custom post types, revisiones | UPDATE, DELETE | `trg_posts_au`, `trg_posts_bd` |
| `{prefijo}_users` | Usuarios del sistema, perfiles, metadatos | UPDATE, DELETE | `trg_users_au`, `trg_users_bd` |
| `{prefijo}_comments` | Comentarios, reviews, respuestas | UPDATE, DELETE | `trg_comments_au`, `trg_comments_bd` |

### � **Estructura de la Tabla de Auditoría**
La tabla `{prefijo_wp}BD_SafeTrigger` captura la siguiente información para cada evento:

```sql
CREATE TABLE {prefijo_wp}BD_SafeTrigger (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    db_user VARCHAR(128),                    -- Usuario de MySQL que ejecutó la operación
    wp_user_id BIGINT UNSIGNED NULL,         -- ID del usuario WordPress (si disponible)
    table_name VARCHAR(128) NOT NULL,        -- Tabla afectada
    action ENUM('UPDATE','DELETE') NOT NULL, -- Tipo de operación
    pk_value VARCHAR(64) NOT NULL,           -- ID del registro afectado
    old_data LONGTEXT,                       -- Datos antes del cambio (JSON)
    client_host VARCHAR(255),                -- Host del cliente
    PRIMARY KEY (id),
    KEY idx_table_time (table_name, event_time),
    KEY idx_action_time (action, event_time),
    KEY idx_wp_user_time (wp_user_id, event_time)
) ENGINE=InnoDB;
```

### 📄 **Ejemplo de Registro de Auditoría**
```json
{
  "id": 1234,
  "event_time": "2025-10-26 10:30:15",
  "db_user": "wp_user@localhost",
  "wp_user_id": 5,
  "table_name": "wp_posts",
  "action": "UPDATE",
  "pk_value": "891",
  "old_data": "{\"post_title\":\"Título anterior\",\"post_content\":\"Contenido anterior\",\"post_status\":\"draft\"}",
  "client_host": "192.168.1.100"
}
```

## 📧 Sistema de Reportes Profesionales

### 📊 **Reportes Automáticos Programados**
Los reportes se generan automáticamente según la configuración y incluyen:

#### **Contenido del Reporte**
- **� Resumen Ejecutivo**: Estadísticas del período (total eventos, actualizaciones, eliminaciones)
- **📋 Actividad por Tabla**: Desglose detallado de eventos por tabla monitoreada
- **🔍 Eventos Recientes**: Lista de los últimos 20 eventos con detalles completos
- **� Gráficos Visuales**: Cards con métricas destacadas y códigos de color
- **🛡️ Información del Sistema**: Estado de triggers, configuración y metadatos

#### **Diseño del Email**
- **HTML Responsivo**: Optimizado para desktop, tablet y móvil
- **Diseño Corporativo**: Colores profesionales y tipografía clara
- **Headers Personalizados**: Información del sitio y timestamp
- **Tablas Estructuradas**: Datos organizados con estilos CSS inline
- **Footer Informativo**: Información del plugin y links de soporte

### 📤 **Tipos de Reportes Disponibles**

#### **1. Reportes Instantáneos**
- Generación bajo demanda desde el panel de administración
- Datos en tiempo real del día actual
- Envío inmediato a la lista de destinatarios configurada
- Perfecto para verificaciones rápidas o investigación de incidentes

#### **2. Reportes Programados**
- **Diarios**: Enviados a la hora configurada cada día
- **Semanales**: Resumen semanal enviado el lunes
- **Mensuales**: Reporte mensual el primer día del mes
- Configuración granular de contenido y destinatarios

### 🎯 **Ejemplo de Reporte Email**

```html
<!-- Extracto del HTML generado -->
<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;">
  <header style="text-align: center; border-bottom: 2px solid #2271b1;">
    <h1 style="color: #2271b1;">🛡️ DB-SafeTrigger</h1>
    <h2>📊 Reporte Diario de Auditoría</h2>
    <p>Mi Sitio WordPress • 2025-10-26 10:30:15</p>
  </header>
  
  <section>
    <h3>📈 Resumen de Actividad - 26/10/2025</h3>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr);">
      <div style="background: #e7f3ff; padding: 20px; text-align: center;">
        <div style="font-size: 32px; color: #0073aa;">47</div>
        <div>Total Eventos</div>
      </div>
      <!-- Más cards de estadísticas -->
    </div>
  </section>
  
  <!-- Tabla de eventos recientes -->
  <!-- Footer con información del sistema -->
</div>
```

## 🔐 Seguridad y Mejores Prácticas

### 🛡️ **Medidas de Seguridad Implementadas**
- **Cifrado de credenciales**: Las API Keys de Mailjet se almacenan cifradas en la base de datos de WordPress
- **Validación de nonces**: Todas las acciones administrativas requieren tokens de seguridad válidos
- **Sanitización de datos**: Todos los inputs son validados y sanitizados antes del procesamiento
- **Prevención de acceso directo**: Todos los archivos PHP incluyen verificación de constante ABSPATH
- **Logs seguros**: No se registran datos sensibles como contraseñas o información personal

### 🔒 **Datos Capturados y Protegidos**
- **Solo datos de auditoría**: Se capturan únicamente los datos necesarios para trazabilidad
- **Exclusión de datos sensibles**: Passwords, tokens de sesión y datos personales no se registran
- **Retención configurable**: Los logs pueden ser purgados automáticamente según políticas internas
- **Acceso controlado**: Solo usuarios con permisos `manage_options` pueden acceder al plugin

### 📋 **Buenas Prácticas Recomendadas**

#### **Configuración de Destinatarios**
- Configura solo emails corporativos responsables del monitoreo de seguridad
- Evita usar emails personales o externos para reportes de auditoría
- Mantén la lista de destinatarios actualizada y revísala periódicamente
- Considera crear un grupo/lista específica para reportes de auditoría

#### **Gestión de Logs**
- Revisa regularmente los logs de auditoría para detectar patrones sospechosos
- Configura alertas para eventos críticos (múltiples eliminaciones, cambios fuera de horario)
- Establece políticas de retención de logs según requerimientos normativos
- Considera exportar logs críticos para almacenamiento a largo plazo

#### **Monitoreo del Sistema**
- Verifica semanalmente que los triggers estén activos en **📊 Estado del Sistema**
- Prueba el envío de reportes mensualmente para asegurar conectividad con Mailjet
- Mantén el plugin actualizado para recibir mejoras de seguridad
- Revisa los logs de errores de PHP para detectar problemas tempranos

#### **Cumplimiento Normativo**
- Documenta las configuraciones de auditoría para auditorías externas
- Establece procedimientos de respuesta ante eventos críticos detectados
- Considera integrar con sistemas SIEM empresariales si es necesario
- Mantén respaldos de los logs de auditoría según políticas corporativas

## 🛠️ Estructura del Proyecto y Desarrollo

### 📁 **Arquitectura del Plugin**
```
DB-SafeTrigger/
├── 📄 db-safetrigger.php              # Archivo principal del plugin
├── 📄 README.md                       # Documentación completa
├── 📄 LICENSE                         # Licencia GPL v2+
├── 📄 uninstall.php                   # Script de desinstalación limpia
├── 📁 admin/                          # Clases de administración (futuro)
│   ├── 📄 class-db-safetrigger-admin.php
│   └── 📁 partials/
│       └── 📄 db-safetrigger-admin-display.php
├── 📁 assets/                         # Recursos CSS y JavaScript
│   ├── 📁 css/
│   │   └── 📄 db-safetrigger-admin.css
│   └── 📁 js/
│       └── 📄 db-safetrigger-admin.js
├── 📁 inc/                           # Clases auxiliares
│   ├── 📄 class-dbst-session.php     # Gestión de sesiones MySQL
│   ├── 📄 class-dbst-cron.php        # Tareas programadas
│   ├── 📄 class-dbst-installer.php   # Instalación y migración
│   ├── 📄 class-dbst-upgrader.php    # Actualizaciones
│   └── 📄 class-dbst-admin.php       # Panel de administración
└── 📁 includes/                      # Clases principales (WordPress standard)
    ├── 📄 class-db-safetrigger-activator.php
    ├── 📄 class-db-safetrigger-deactivator.php
    ├── 📄 class-db-safetrigger-i18n.php
    ├── 📄 class-db-safetrigger-loader.php
    └── 📄 class-db-safetrigger.php
```

### 🔧 **Componentes Principales**

#### **Archivo Principal** (`db-safetrigger.php`)
- **2000+ líneas de código** con todas las funcionalidades principales
- **Funciones de administración**: Interfaz completa de 5 pestañas
- **Sistema de triggers**: Creación y gestión automática
- **Integración Mailjet**: Send API v3.1 completa
- **Hooks y filtros**: 20+ puntos de extensión para desarrolladores

#### **Clase DBST_Session** (`inc/class-dbst-session.php`)
- **Sistema avanzado de captura de usuario WordPress**
- **15+ hooks de interceptación** para operaciones CRUD
- **Variables de sesión MySQL** para mantener contexto
- **Interceptación de consultas WPDB** con filtros dinámicos
- **Logging detallado** para debugging y troubleshooting

#### **Script de Desinstalación** (`uninstall.php`)
- **Limpieza completa y segura** del sistema
- **Eliminación de triggers** con verificación de nombres
- **Borrado de tablas** con validación de prefijos
- **Limpieza de opciones** (30+ configuraciones)
- **Método de limpieza en múltiples fases** para compatibilidad

### 🎯 **Hooks y Filtros para Desarrolladores**

El plugin proporciona múltiples puntos de extensión para personalización avanzada:

```php
// Hook ejecutado después de crear triggers exitosamente
do_action('dbst_triggers_created', $trigger_count, $created_triggers);

// Hook ejecutado antes de enviar cualquier reporte
do_action('dbst_before_send_report', $report_data, $recipients);

// Hook ejecutado después de enviar reporte (con resultado)
do_action('dbst_after_send_report', $result, $report_data);

// Filtro para modificar destinatarios de reportes
$recipients = apply_filters('dbst_report_recipients', $recipients, $report_type);

// Filtro para personalizar contenido del reporte
$content = apply_filters('dbst_report_content', $content, $stats, $events);

// Filtro para modificar configuración de Mailjet
$mailjet_config = apply_filters('dbst_mailjet_config', $config);

// Filtro para personalizar datos capturados en auditoría
$audit_data = apply_filters('dbst_audit_data', $data, $table_name, $action);
```

### 📊 **Métricas de Desarrollo**
- **Código**: 100% PHP (WordPress Coding Standards)
- **Líneas de código**: 2000+ líneas activas
- **Clases**: 8 clases principales + auxiliares
- **Funciones**: 50+ funciones documentadas
- **Compatibilidad**: WordPress 5.0+ / PHP 7.4+
- **Base de datos**: MySQL 5.7+ / MariaDB 10.2+
- **APIs**: Mailjet Send API v3.1 completa

## � Solución de Problemas y FAQ

### ❓ **Problemas Comunes y Soluciones**

#### **🔧 Los triggers no se crean correctamente**
**Síntomas**: Mensaje de error al hacer clic en "Crear/Actualizar Triggers"
- **Causa 1**: Falta de privilegios TRIGGER en la base de datos
  - **Solución**: Contacta tu proveedor de hosting para habilitar privilegios TRIGGER
  - **Verificación**: En phpMyAdmin, ejecuta `SHOW GRANTS FOR CURRENT_USER();`
- **Causa 2**: Usuario de MySQL con permisos limitados
  - **Solución**: Verifica que el usuario tenga permisos CREATE, ALTER, DROP además de SELECT/INSERT/UPDATE/DELETE
- **Causa 3**: Restricciones del hosting compartido
  - **Solución**: Algunos hostings bloquean la creación de triggers por seguridad, consulta con soporte técnico

#### **📧 Los emails no se envían**
**Síntomas**: Error al enviar reportes o no se reciben emails
- **Causa 1**: Credenciales de Mailjet incorrectas
  - **Solución**: Verifica API Key y Secret Key en tu panel de Mailjet
  - **Verificación**: Ve a **📧 Configuración Mailjet** y confirma el estado de cada credencial
- **Causa 2**: Email remitente no verificado en Mailjet
  - **Solución**: En Mailjet, ve a "Sender domains & addresses" y verifica tu dominio/email
- **Causa 3**: Límites de Mailjet alcanzados
  - **Solución**: Revisa tu cuota mensual en el dashboard de Mailjet
- **Causa 4**: Firewall o restricciones de red
  - **Solución**: Verifica que tu hosting permita conexiones HTTPS salientes al puerto 443

#### **📊 No aparecen logs en la auditoría**
**Síntomas**: La tabla de logs está vacía o no se registran eventos
- **Causa 1**: Los triggers no están activos
  - **Solución**: Ve a **🔧 Gestión de Triggers** y verifica que estén en estado "✅ Activo"
- **Causa 2**: Variable de usuario WordPress no se establece
  - **Solución**: Verifica en los logs de PHP si aparecen mensajes "DBST: Usuario establecido"
- **Causa 3**: Operaciones realizadas por procesos automáticos
  - **Solución**: Los triggers solo capturan operaciones con usuario WordPress válido

#### **🔄 Error "Plugin has been deactivated"**
**Síntomas**: El plugin se desactiva automáticamente
- **Causa**: Error fatal en PHP por incompatibilidad o falta de recursos
  - **Solución 1**: Aumenta la memoria PHP en wp-config.php: `ini_set('memory_limit', '256M');`
  - **Solución 2**: Revisa los logs de errores de PHP para identificar el problema específico
  - **Solución 3**: Desactiva otros plugins temporalmente para verificar conflictos

### 🛠️ **Herramientas de Diagnóstico**

#### **Verificación del Estado del Sistema**
1. Ve a **📊 Estado del Sistema** en el panel del plugin
2. Verifica que todos los indicadores estén en verde
3. Revisa el conteo de triggers activos (debería ser 6)
4. Confirma que la tabla de auditoría existe

#### **Prueba de Conectividad con Mailjet**
1. Ve a **📧 Configuración Mailjet**
2. Completa las credenciales
3. Ve a **📋 Reportes** y haz clic en "📤 Enviar Reporte Ahora"
4. Verifica que recibas el email en pocos minutos

#### **Verificación Manual de Triggers**
Ejecuta en phpMyAdmin o terminal MySQL:
```sql
SHOW TRIGGERS LIKE '%trg_%';
```
Deberías ver 6 triggers: trg_posts_au, trg_posts_bd, trg_users_au, trg_users_bd, trg_comments_au, trg_comments_bd

### 📞 **Cómo Obtener Soporte**

#### **Información a Incluir en Reportes de Errores**
1. **Versión del plugin**: DB-SafeTrigger v1.1.0
2. **Versión de WordPress**: Ve a Escritorio → Actualizaciones
3. **Versión de PHP**: Ve a Herramientas → Salud del sitio → Información
4. **Tipo de hosting**: Compartido, VPS, dedicado
5. **Mensaje de error exacto**: Copia y pega el error completo
6. **Logs de PHP**: Revisa `/wp-content/debug.log` si tienes debug habilitado

#### **Canales de Soporte**
- **GitHub Issues**: [DB-SafeTrigger Issues](https://github.com/AlfieriMora/DB-SafeTrigger/issues)
- **Documentación**: Este README
- **Email de soporte**: Disponible para clientes empresariales

## � Licencia y Contribuciones

### 📄 **Licencia**
Este plugin está licenciado bajo **GPL v2 o posterior**. Ver [LICENSE](LICENSE) para detalles completos.

**Esto significa que puedes:**
- ✅ Usar el plugin comercialmente
- ✅ Modificar el código fuente
- ✅ Distribuir copias modificadas
- ✅ Usar en proyectos propietarios

**Con las siguientes condiciones:**
- 📋 Mantener la licencia GPL en derivados
- 📋 Proporcionar código fuente de modificaciones si distribuyes
- 📋 Mantener créditos de autoría original

### 🤝 **Contribuciones y Desarrollo**

#### **Cómo Contribuir**
Las contribuciones son bienvenidas y ayudan a mejorar el plugin para toda la comunidad:

1. **Fork el repositorio**: Haz fork de [DB-SafeTrigger](https://github.com/AlfieriMora/DB-SafeTrigger)
2. **Crea una rama feature**: `git checkout -b feature/nueva-funcionalidad`
3. **Desarrolla tu mejora**: Sigue los estándares de WordPress Coding Standards
4. **Prueba tu código**: Verifica compatibilidad con múltiples versiones de WordPress
5. **Commit tus cambios**: `git commit -am 'Añadir nueva funcionalidad: descripción'`
6. **Push a tu rama**: `git push origin feature/nueva-funcionalidad`
7. **Crea Pull Request**: Describe claramente los cambios y beneficios

#### **Áreas de Contribución Prioritarias**
- 🌐 **Internacionalización**: Traducción a otros idiomas
- 🔧 **Optimización**: Mejoras de rendimiento y eficiencia
- 📊 **Reportes**: Nuevos tipos de reportes y visualizaciones
- 🔒 **Seguridad**: Auditorías de seguridad y mejoras
- 📱 **UI/UX**: Mejoras en la interfaz de usuario
- 🧪 **Testing**: Casos de prueba automatizados

#### **Estándares de Código**
- **WordPress Coding Standards**: Sigue las [guías oficiales](https://developer.wordpress.org/coding-standards/)
- **Documentación**: Documenta todas las funciones públicas con PHPDoc
- **Comentarios**: Código comentado en español e inglés
- **Versionado Semántico**: Usa [SemVer](https://semver.org/) para versionado

### 👥 **Equipo de Desarrollo**

#### **Desarrolladores Principales**
- **[Alfieri Mora](https://github.com/AlfieriMora)** - Arquitecto Principal y Lead Developer
- **Kriscia Campos** - Especialista en Integración Mailjet
- **Ernesto Valerio** - Especialista en Base de Datos y Triggers
- **Eddy Alfaro** - Especialista en UI/UX y Frontend

#### **Reconocimientos**
- **Comunidad WordPress**: Por las bases sólidas y estándares de desarrollo
- **Mailjet**: Por la excelente API de envío de emails
- **Contribuidores**: Todas las personas que han reportado bugs y sugerido mejoras

### 🎯 **Roadmap de Desarrollo**

#### **Versión 1.2.0 (Próxima)**
- 🌐 Soporte multiidioma (español, inglés, francés)
- 📊 Dashboard con gráficos interactivos
- 🔔 Sistema de alertas en tiempo real
- 📱 Interfaz responsive mejorada

#### **Versión 1.3.0 (Futuro)**
- 🔗 Integración con Slack y Discord
- 📈 Exportación de reportes a PDF/Excel
- 🔍 Sistema de búsqueda avanzada en logs
- 🧪 API REST para integraciones externas

#### **Versión 2.0.0 (Futuro Lejano)**
- 🏗️ Arquitectura modular con addons
- 🌊 Soporte para WordPress Multisite
- 🔒 Integración con sistemas SIEM
- 📊 Machine Learning para detección de anomalías

---

**Desarrollado con ❤️ para la comunidad WordPress por [Alfieri Mora](https://github.com/AlfieriMora) y equipo**

*¿Te ha sido útil este plugin? ⭐ Dale una estrella en [GitHub](https://github.com/AlfieriMora/DB-SafeTrigger) y compártelo con otros desarrolladores.*
