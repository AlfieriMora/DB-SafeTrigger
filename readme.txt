=== DB-SafeTrigger ===
Contributors: alfierimora
Tags: audit, database, security, logging, traceability
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sistema de Auditoría y Trazabilidad de Base de Datos para WordPress.

== Description ==

DB-SafeTrigger es un plugin avanzado de auditoría que monitorea automáticamente los cambios en la base de datos de WordPress usando triggers MySQL. Proporciona trazabilidad completa de todas las modificaciones y eliminaciones en las tablas principales.

**Características principales:**

* Monitoreo automático de cambios usando triggers MySQL
* Auditoría de tablas: posts, users, comments
* Sistema de reportes por email con Mailjet
* Interfaz de administración intuitiva
* Soporte multiidioma (Español/Inglés)
* Logs detallados con información de usuario
* Configuración automática de triggers

== Installation ==

1. Sube el plugin al directorio `/wp-content/plugins/`
2. Activa el plugin desde el menú 'Plugins' en WordPress
3. Ve a Configuración > DB-SafeTrigger para configurar
4. Crea los triggers automáticamente desde la pestaña "Gestión de Triggers"

== Frequently Asked Questions ==

= ¿Es compatible con todos los hostings? =

El plugin requiere permisos para crear triggers MySQL. Algunos hostings compartidos pueden no permitir esto.

= ¿Afecta el rendimiento del sitio? =

El impacto es mínimo ya que usa triggers nativos de MySQL que son muy eficientes.

== Screenshots ==

1. Panel de estado del sistema
2. Gestión de triggers
3. Configuración de Mailjet
4. Logs de auditoría

== Changelog ==

= 1.1.0 =
* Sistema de internacionalización completo
* Soporte para español e inglés
* Mejoras en la interfaz de usuario
* Sistema de fallback para traducciones
* Optimización de triggers MySQL

= 1.0.0 =
* Versión inicial
* Auditoría básica de base de datos
* Integración con Mailjet
* Sistema de reportes

== Upgrade Notice ==

= 1.1.0 =
Esta versión incluye mejoras importantes en internacionalización y estabilidad.