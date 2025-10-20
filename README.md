# 🔒 DB-SafeTrigger

Plugin de **Trazabilidad y Auditoría a Nivel de Base de Datos** para WordPress con integración completa de **Mailjet v3.1**.

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](LICENSE)
[![Mailjet](https://img.shields.io/badge/Mailjet-v3.1-orange.svg)](https://www.mailjet.com/)

## 📋 Descripción

DB-SafeTrigger proporciona un sistema completo de auditoría para WordPress que monitorea automáticamente los cambios en la base de datos y envía reportes detallados por email usando Mailjet.

### ✨ Características Principales

- 🔍 **Monitoreo Automático**: Triggers de base de datos para detectar cambios en tiempo real
- 📧 **Reportes por Email**: Integración completa con Mailjet Send API v3.1
- 📊 **Dashboard Completo**: Panel de administración con 5 pestañas especializadas
- 🔒 **Auditoría Completa**: Registro detallado de UPDATE y DELETE en tablas críticas
- ⚡ **Reportes Automáticos**: Envío programado de reportes diarios
- 🎯 **Fácil Configuración**: Interfaz intuitiva para setup rápido

## 🚀 Instalación

### Método 1: Instalación desde WordPress Admin

1. Descarga `db-safetrigger.zip`
2. Ve a **Plugins → Añadir nuevo → Subir plugin**
3. Selecciona el archivo ZIP y haz clic en **Instalar ahora**
4. **Activa** el plugin

### Método 2: Instalación Manual

1. Extrae los archivos del plugin
2. Sube la carpeta `db-safetrigger` a `/wp-content/plugins/`
3. Activa el plugin desde el panel de WordPress

## ⚙️ Configuración

### 1️⃣ Configuración Inicial

Después de activar el plugin, ve a **Ajustes → DB-SafeTrigger**:

1. **📊 Estado del Sistema**: Verifica que todo esté funcionando
2. **🔧 Gestión de Triggers**: Crea los triggers de auditoría
3. **📧 Configuración Mailjet**: Configura tus credenciales de API

### 2️⃣ Configurar Mailjet

Para obtener tus credenciales de Mailjet:

1. Regístrate en [Mailjet](https://www.mailjet.com/)
2. Ve a **Account Settings → API Keys**
3. Copia tu **API Key** y **Secret Key**
4. Configúralos en **📧 Configuración Mailjet**

### 3️⃣ Crear Triggers de Auditoría

1. Ve a **🔧 Gestión de Triggers**
2. Haz clic en **🚀 Crear/Actualizar Triggers de Auditoría**
3. Verifica en **📊 Estado del Sistema** que los triggers estén activos

## 📊 Funcionalidades

### Panel de Administración (5 Pestañas)

#### 📊 Estado del Sistema
- Verificación completa del sistema
- Estadísticas de auditoría en tiempo real
- Estado de triggers y configuración

#### 🔧 Gestión de Triggers
- Creación y gestión de triggers automáticos
- Monitoreo de tablas: `posts`, `users`, `comments`
- Detección de cambios UPDATE y DELETE

#### 📧 Configuración Mailjet
- Setup completo de Mailjet Send API v3.1
- Configuración de remitente y destinatarios
- Pruebas de conexión integradas

#### 📋 Reportes
- Envío de reportes de prueba
- Configuración de reportes automáticos
- Programación de envíos diarios

#### 📜 Logs de Auditoría
- Visualización paginada de todos los logs
- Filtros por fecha y tipo de acción
- Información detallada de cada cambio

### Características Técnicas

#### 🔒 Sistema de Auditoría
- **Triggers automáticos** para UPDATE y DELETE
- **Tabla optimizada** `log_auditoria` con índices
- **Registro completo** con timestamps y usuarios
- **Compatible** con hosting compartido

#### 📧 Integración Mailjet v3.1
- **Send API v3.1** completa con todas las características
- **CustomID, CustomCampaign, URLTags** para tracking
- **HTML emails** profesionales con estadísticas
- **Manejo robusto** de errores y reintentos

#### 📋 Reportes Automáticos
- **Cron jobs** de WordPress para envío programado
- **Estadísticas detalladas** de actividad
- **Emails HTML** responsivos y profesionales
- **Múltiples destinatarios** configurables

## 🔧 Requisitos del Sistema

- **WordPress**: 5.0 o superior
- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior / MariaDB 10.2 o superior
- **Privilegios TRIGGER**: Recomendado (funciona sin ellos en algunos hostings)

## 📝 Tablas Monitoreadas

El plugin monitorea automáticamente las siguientes tablas de WordPress:

| Tabla | Descripción | Acciones |
|-------|-------------|----------|
| `wp_posts` | Entradas, páginas, custom posts | UPDATE, DELETE |
| `wp_users` | Usuarios del sistema | UPDATE, DELETE |
| `wp_comments` | Comentarios y reviews | UPDATE, DELETE |

## 🚨 Estructura de Logs

Cada entrada en el log de auditoría contiene:

```json
{
  "id": "ID único del log",
  "event_time": "2025-10-20 04:29:00",
  "db_user": "usuario_bd@servidor",
  "table_name": "wp_posts",
  "action": "UPDATE|DELETE",
  "pk_value": "123",
  "old_data": "{\"id\": 123}",
  "client_host": "localhost"
}
```

## 📧 Ejemplo de Reporte Email

Los reportes incluyen:

- 📊 **Estadísticas**: Total logs, actividad del día, actividad semanal
- 📋 **Actividad Reciente**: Últimos 10 cambios detectados
- 🎯 **Información del Sistema**: Estado de triggers y configuración
- 💌 **Diseño Profesional**: HTML responsivo con estilos corporativos

## 🔐 Seguridad

### Datos Protegidos
- Las credenciales de Mailjet se almacenan cifradas en WordPress
- Los logs contienen solo información necesaria para auditoría
- No se registran datos sensibles como contraseñas

### Buenas Prácticas
- Configura destinatarios de email responsables
- Revisa regularmente los logs de auditoría
- Mantén el plugin actualizado

## 🛠️ Desarrollo

### Estructura del Proyecto
```
db-safetrigger/
├── db-safetrigger.php          # Archivo principal del plugin
├── README.md                   # Documentación
├── LICENSE                     # Licencia GPL v2+
├── .gitignore                  # Archivos ignorados por Git
├── verificacion-sql.sql        # Scripts SQL de verificación
└── uninstall.php              # Script de desinstalación
```

### Hooks y Filtros

El plugin proporciona hooks para personalización:

```php
// Hook ejecutado después de crear triggers
do_action('dbst_triggers_created', $trigger_count);

// Hook ejecutado antes de enviar reporte
do_action('dbst_before_send_report', $report_data);

// Filtro para modificar destinatarios de reporte
$recipients = apply_filters('dbst_report_recipients', $recipients);
```

## 🐛 Solución de Problemas

### Problema: Los triggers no se crean
- **Causa**: Falta de privilegios TRIGGER en la base de datos
- **Solución**: Contacta tu proveedor de hosting para habilitar privilegios

### Problema: Los emails no se envían
- **Causa**: Credenciales de Mailjet incorrectas
- **Solución**: Verifica API Key y Secret Key en la configuración

### Problema: No aparecen logs
- **Causa**: Los triggers no están activos
- **Solución**: Ve a **🔧 Gestión de Triggers** y créalos

## 📞 Soporte

- **Documentación**: Este README
- **Issues**: [GitHub Issues](https://github.com/AlfieriMora/DB-SafeTrigger/issues)
- **Autor**: Alfieri Mora

## 📄 Licencia

Este plugin está licenciado bajo GPL v2 o posterior. Ver [LICENSE](LICENSE) para más detalles.

## 🙏 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Añadir nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crea un Pull Request

## 📊 Estadísticas

- **Código**: 100% PHP
- **Compatibilidad**: WordPress 5.0+
- **API**: Mailjet Send v3.1
- **Base de datos**: MySQL/MariaDB triggers

---

**Desarrollado con ❤️ por [Alfieri Mora](https://github.com/AlfieriMora)**
