# Lumen

> Sistema de gestión escolar

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4-FB70A9?logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://php.net)
[![Version](https://img.shields.io/badge/versión-v1.4.2-blue)]()

Proyecto de tesis — *Licenciatura en Enseñanza de la Computación e Informática*  
EFPEM, Universidad de San Carlos de Guatemala

---

## Índice

- [Descripción](#descripción)
- [Stack tecnológico](#stack-tecnológico)
- [Requisitos del sistema](#requisitos-del-sistema)
- [Instalación](#instalación)
- [Variables de entorno](#variables-de-entorno)
- [Módulos implementados](#módulos-implementados)
- [Historial de versiones](#historial-de-versiones)

---

## Descripción

**EduCheck** es un sistema de gestión escolar desarrollado en Laravel 12 + Livewire 4, diseñado para cubrir los procesos académicos y administrativos del Instituto Clemente Martínez Rojas. El nombre interno de la aplicación es **Lumen** (`APP_NAME`).

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.3, Laravel 12, Livewire 4 |
| Frontend | AdminLTE v3.15.3, Bootstrap 4 |
| Autenticación y permisos | Laravel Fortify, Spatie Permission |
| Reportes | FPDF (PDFs personalizados), Maatwebsite Excel |
| UI / Gráficos | SweetAlert2, Chart.js v4.4.0 |
| Base de datos | MySQL — servidor `deproweb.net` |

---

## Requisitos del sistema

- PHP **>= 8.3**
- Composer
- Node.js (compilación de assets)
- MySQL
- Extensiones PHP: `iconv`, `mbstring`, `gd` *(necesaria para PDFs con imágenes)*

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd <nombre-del-proyecto>

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JS y compilar assets
npm install && npm run build

# 4. Configurar el entorno
cp .env.example .env
# Editar .env con los valores correspondientes (ver sección de variables de entorno)

# 5. Generar la clave de la aplicación
php artisan key:generate

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Crear el enlace simbólico de storage
php artisan storage:link

# 8. Ajustar permisos de directorios
chmod -R 775 storage/ bootstrap/cache/
```

### Credenciales por defecto (seeder)

Al ejecutar `php artisan migrate --seed`, el seeder crea automáticamente un usuario **Super Administrador** con el que se puede ingresar al sistema por primera vez:

| Campo | Valor |
|---|---|
| Correo | `superadmin@lumen.test` |
| Contraseña | `password` |

> ⚠️ **Cambia la contraseña inmediatamente** después del primer inicio de sesión en un entorno de producción.

---

## Variables de entorno

A continuación se describen las variables más relevantes del archivo `.env`:

```dotenv
# Nombre interno de la aplicación — NO modificar
APP_NAME=Lumen

# Nombre de la institución (se usa en PDFs, correos y encabezados)
# Debe estar codificado en UTF-8; verificar si contiene tildes o caracteres especiales
APP_INSTITUTION_NAME="Instituto Clemente Martínez Rojas"

# Ruta al logo de la institución (relativa a storage/app/public o absoluta)
APP_INSTITUTION_LOGO_IMG=logos/logo.png

# Base de datos
DB_CONNECTION=mysql
DB_HOST=deproweb.net
DB_PORT=3306
DB_DATABASE=nombre_bd
DB_USERNAME=usuario
DB_PASSWORD=contraseña

# SMTP para correos de restablecimiento de contraseña
MAIL_MAILER=smtp
MAIL_HOST=smtp.proveedor.com
MAIL_PORT=587
MAIL_USERNAME=correo@dominio.com
MAIL_PASSWORD=contraseña_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=correo@dominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

> **Nota sobre UTF-8:** Si `APP_INSTITUTION_NAME` contiene tildes u otros caracteres especiales, asegúrate de que el archivo `.env` esté guardado en codificación **UTF-8 sin BOM** para evitar problemas en la generación de PDFs y correos.

---

## Módulos implementados

- **Gestión de usuarios, roles y permisos** — integración con Spatie Permission
- **Inscripciones** — estudiantes, guardianes y ficha médica
- **Estructura académica** — niveles, grados, secciones y aulas
- **Pénsum y cursos** — administración por grado
- **Asignación docente** — cursos a profesores por unidades
- **Cuadros de calificaciones** — actividades, scores, mejoras y totales
- **Configuración académica por ciclo escolar** — actividades predefinidas o libres para los profesores
- **Flujo de aprobación/rechazo de cuadros**
- **Solicitudes de cambio de notas**
- **Toma de asistencia** — registro por sección y fecha
- **Dashboards con gráficos** — vistas para administrador y profesor
- **Reportes PDF y Excel** — sábanas, boletas, cuadros, listados, actividades no entregadas y asistencia
- **Auditoría general** — registro de eventos del sistema
- **Autenticación** — reset de contraseña con plantilla HTML personalizada
- **Cambio forzado de contraseña** en primer ingreso *(en desarrollo)*

---

## Historial de versiones

| Versión | Descripción |
|---|---|
| `v1.0.0` | Base del sistema |
| `v1.1.0` | Reportes PDF y Excel |
| `v1.2.0` | Solicitudes de cambio de notas y dashboards |
| `v1.3.0` | Inscripciones de estudiantes |
| `v1.4.0` | Auditoría general y reorganización del menú |
| `v1.4.1` | Correcciones PDF y validaciones |
| `v1.4.2` | Fix autenticación y correo reset |
| `v1.5.0` | Toma de asistencia y modal de re-login al expirar la sesión |

---

*Desarrollado como proyecto de tesis — EFPEM, USAC — Guatemala*
