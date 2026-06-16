# EduCheck — CLAUDE.md

## Descripción del proyecto
Sistema de gestión escolar — proyecto de tesis EFPEM, Universidad de San Carlos de Guatemala. Autorizado para el Instituto Clemente Martínez Rojas. **v2.1.0**

## Stack tecnológico
- PHP 8.3 / Laravel 12 / Livewire 4
- AdminLTE v3.15.3 / Bootstrap 4 (sin Tailwind)
- Spatie Permission v7 / Laravel Fortify v1
- FPDF (`app/Helpers/PDF.php`), Maatwebsite Excel v3.1
- SweetAlert2 / Chart.js v4.4.0
- MySQL / Session: database / Queue: database

## Variables de entorno clave
- `APP_NAME=Lumen` — nunca cambiar
- `APP_INSTITUTION_NAME="Instituto Clemente Martínez Rojas"`
- `APP_LOCALE=es_GT` / `MAIL_MAILER=log` (dev), SMTP (prod)

## Convenciones de código
- Código PHP/Laravel en **inglés**; textos de usuario en **español**
- Rutas, permisos, prefijos URL y archivos de rutas: siempre en inglés. Textos visibles del menú y descripciones de permisos: en español.
- `use` imports siempre. Llaves en toda estructura de control. Return types explícitos.
- Casts en método `casts(): array`. Constructor property promotion. Enums en TitleCase.
- **Permisos siempre al final de `database/seeders/RoleSeeder.php`** — nunca crear seeders separados.
- Comentarios solo cuando el WHY es no obvio.

## Estructura de rutas
- `routes/web.php` — login, dashboard, profile, reauth
- `routes/admin.php` — administrador
- `routes/profesor.php` — profesor
- `routes/student.php` — estudiante (prefijo `/student`)
- `routes/settings.php` — perfil/contraseña/2FA
- Middleware permisos: `can:nombre.del.permiso`
- Middleware personalizado: `force.password.change` → `EnsurePasswordIsChanged`
- Registro en `bootstrap/app.php`

## Roles del sistema
| # | Nombre | Variable |
|---|---|---|
| 1 | Super Administrador | `$role1` |
| 2 | Director | `$role2` |
| 3 | Estudiante | `$role3` |
| 4 | Profesor | `$role4` |
| 5 | Secretaria | `$role5` |
| 6 | Caja | `$role6` |
| 7 | Orientador | `$role7` |
| 8 | Coordinador | `$role8` |

Convención de permisos: `admin.recurso.accion`, `profesor.recurso.accion`, `dashboard.panel.nombre`.

## Autenticación
- **Laravel Fortify**: login, register, reset password, 2FA (TOTP). Rate limiting: 5 intentos/min.
- **Forced Password Change**: middleware `EnsurePasswordIsChanged` redirige a `/forzar-cambio-clave` si `users.must_change_password = true`.
- **Re-autenticación**: `POST /reauth` para sesiones expiradas.
- Session: database driver, 120 min.

## Modelos principales (`app/Models/`)

### Usuarios y personas
- `User` — `HasRoles` + `TwoFactorAuthenticatable`; relaciones: `hasOne(Student/Professor/MedicalRecord)`, `morphOne(Image)`, `hasMany(AuditLog)`
- `Student`, `Professor`, `Guardian`, `MedicalRecord`, `Image` (polimórfica)

### Estructura académica
- `Level` → `Grade` → `Section` → `Classroom` (year + level + grade + section)
- `StudentEnrollment` — student_id, classroom_id, status
- `Pensum` — grade_id, year, units, unit_percentages (array); `getUnitPercentage(int $unit): float`
- `PensumCourse` — jerárquico (parent_id), is_main, is_official, ordering
- `ClassroomCourseAssignment` — classroom_id, professor_id, pensum_course_id, unit

### Cuadros de calificaciones
- `AcademicConfiguration` — mode (free/assigned), improvement_type, improvement_percentage
- `GradeBook` — status: `open`→`locked`→`approved`/`rejected`
- `GradeBookActivity`, `GradeBookScore` (score + improvement_score), `GradeBookTotal` (total = `ceil(normal + extra)`)
- `GradeChangeRequest` / `GradeChangeRequestItem`

### Admisiones
- `AdmissionApplication` — datos del alumno, padre, madre, encargado, familia; `current_status`; flags `billing_unlocked`, `psychometric_unlocked`, `academic_unlocked`; métodos: `fullStudentName()`, `guardianNit()`, `guardianTypeLabel()`, `statusLabel()`, `statusColor()`, `hasAllStagesCompleted()`
- `AdmissionApplicationStatus` — historial de estados; `labelFor(string $status)`, `colorFor(string $status)`
- `AdmissionApplicationDocument` — 5 checkboxes de papelería; `fields(): array`, `isComplete(): bool`
- `AdmissionBilling` — invoice_number, invoice_date, user_id (1:1 con application)
- `AdmissionPsychometric` — result, notes (HTML Quill), user_id (1:1 con application)
- `AdmissionCourse` — catálogo de materias de admisión (name, ordering)
- `AdmissionAcademicScore` — admission_application_id, admission_course_id, score, user_id; unique por solicitud+materia

### Otros
- `EnrollmentPeriod` — `activeForEnrollments(): bool`, `activeForDataUpdates(): bool`, sin solapamiento por flag
- `AttendanceRecord` / `AttendanceEntry` — 1 record = 1 día; present:boolean por alumno
- `AuditLog` — user_id, event, module, description, old_values, new_values, ip_address
- `SystemSetting` — configuraciones globales clave-valor (`enrollment_mode`: direct/admissions)

## Flujo de admisiones
**Estados:** `pending` → `emailed` → `reviewed` → `billed` → `psychometric` → `academic` → `accepted` / `rejected`

- **Papelería completa** = 5 checkboxes + `url_documents` + `url_payment` → transiciona `emailed`↔`reviewed` automáticamente via `syncDocumentStatus()` (privado en `AdmissionList`)
- **Desbloqueo para corrección**: flags booleanos en `admission_applications`; permisos `*.unlock` solo para Super Admin; al guardar el flag se apaga sin cambiar `current_status`
- **Aceptar**: solo cuando `current_status === 'academic'` y `hasAllStagesCompleted()` (billing + psychometric presentes)
- **Rechazar**: solo en estado `pending`

## Flujo de aprobación de cuadros
`open` → `locked` (profesor bloquea) → `approved`/`rejected` (admin revisa) → si rechazado, profesor reabre a `open`

## Servicios (`app/Services/`)
- **`GradeBookCalculationService`** — `recalculateAll(GradeBook, $students)`, `recalculateForStudents(GradeBook, $ids)`; fórmula: `ceil(normal + extra)`
- **`AuditService`** — métodos estáticos, captura IP. Módulos cubiertos:
  - Cuadros: `gradeBookStatusChanged`, `scoreChanged`, `gradeScoresCopied`
  - Inscripciones: `enrollmentCreated`, `enrollmentStatusChanged`
  - Usuarios: `userCreated`, `userUpdated`, `professorProfileUpdated`, `medicalRecordUpdated`
  - Cambio de Notas: `gradeChangeRequestCreated`, `gradeChangeRequestResolved`
  - Seguridad: `passwordChanged`
  - Configuración: `configChanged`
  - **Admisiones**: `admissionStatusChanged`, `admissionUpdated`, `admissionDocumentToggled`, `admissionUnlocked`, `admissionBillingSaved`, `admissionPsychometricSaved`, `admissionScoreChanged`, `admissionEvaluationFinalized`, `admissionReportDownloaded`

## Componentes Livewire (`app/Livewire/`)

### Dashboard — 13 paneles independientes con `dashboard.panel.*`
Cada panel usa `wire:init="loadData"` y `style="display: contents;"` en el div raíz para que los `col-*` participen en el row de Bootstrap. Paneles de Profesor requieren null-check en `Auth::user()->professor`.

### Admin (`app/Livewire/Admin/`)
| Componente | Responsabilidad |
|---|---|
| `Levels`, `Grades`, `Sections`, `Classrooms` | CRUD básico |
| `Courses`, `Pensums` | Gestión curricular |
| `ClassroomCourseAssignments` | Asignación profesor-curso; reemplazo en 3 escenarios; cuadro `rejected` se reabre al transferir |
| `AcademicConfigurations` | Config por ciclo escolar |
| `GradeBooks` | Listado + aprobación/rechazo |
| `GradeChangeRequests` | Gestión de solicitudes de cambio |
| `AuditLog` | Auditoría; arrays anidados en old/new se renderizan como mini-tablas |
| `Professors`, `Guardians` | Listado + edición de datos adicionales |
| `Roles/ShowRoles`, `Permissions/ShowPermissions` | Gestión Spatie |
| `Students/EnrollmentList` | Inscripciones |
| `Students/AdmissionList` | Gestión de solicitudes: papelería, cambios de estado, desbloqueos; tabs Alpine.js; confirmaciones SweetAlert2; auditoría completa |
| `Students/AdmissionBillingList` | Facturación: modal editable (sin factura) o solo lectura (con factura); transiciona a `billed` |
| `Students/AdmissionPsychometricList` | Psicométrica: editor Quill; transiciona a `psychometric`; solo lectura en estados posteriores |
| `Students/AdmissionAcademicList` | Académico: agregar/eliminar punteos; finalizar transiciona a `academic`; permiso `admin.admissions.academic` (Coordinador + Super Admin) |
| `Students/AdmissionReport` | Reporte filtrable + exportación Excel de solicitudes; audita descarga con filtros activos |
| `Students/StudentSelector` | Copia de calificaciones entre cuadros con auditoría snapshot |
| `Admin/AdmissionCourses` | CRUD de materias de admisión |
| `Admin/SystemSettings` | Configuraciones globales (`enrollment_mode`) |
| `Reports/*` | Sábanas, boletas PDF, asistencia, riesgo, historial, carga docente, actividades |

### Profesor (`app/Livewire/Profesor/`)
- `GradeBooks` — edición de cuadros
- `GradeBookGrid` — cuadrícula tipo Excel (Alpine.js); lookup de notas con cast `(int)` explícito — nunca `===` para IDs (PDO devuelve strings en producción)
- `GradeChangeRequests`, `TakeAttendance`, `Reports/*`

### Estudiante (`app/Livewire/Estudiante/`)
- `MyGrades`, `MyAttendance`, `MyReportCard` — todo solo lectura, solo cuadros `approved`, solo datos del alumno autenticado

### Formulario público de admisiones
`/admisiones` (sin auth) — 7 secciones; ciclo escolar: ene–jun muestra año actual + siguiente, jul–dic solo el siguiente.

### Sistema QR de actualización de datos
- `GET /actualizar-datos` → `StudentDataRequest` (verificación identidad)
- `GET /actualizar-datos/{token}` → `StudentDataController::verifyToken` → `StudentDataUpdateForm`
- Token en cache 60 min; `StudentDataUpdate` registra `completed_at` + `ip_address`

## Patrones Livewire establecidos

**Crear siempre con `--class`:**
```bash
php artisan make:livewire Admin/NombreComponente --class
```

**Handlers de eventos por componente** (no globales), en bloque `@script`:
```js
$wire.on('showAlert', (params) => { /* SweetAlert */ });
$wire.on('toastMessage', (params) => { /* toast */ });
```
Dispatch con array: `$this->dispatch('showAlert', ['title' => '...', 'type' => 'success'])`.

**Buscadores:** `autocomplete="new-password"` (no `"off"`, Chrome lo ignora).

**Query string + reset de página:**
```php
protected $queryString = ['search', 'filterYear', 'filterStatus', 'cant'];
public function updatingSearch(): void { $this->resetPage(); }
```

**Tabs con Alpine.js** (en lugar de `data-toggle="tab"` de Bootstrap): preservan pestaña activa durante re-renders Livewire.

## Menú (`config/adminlte.php`) — secciones principales
- **ADMINISTRACIÓN** (`admin.menu.encabezado`) → Personal, Gestión Estudiantil, **Proceso de Admisiones** (`admin.menu.admisiones`), Gestión Académica, Cuadros, Reportes, Configuración, Auditoría
- **DOCENTE** (`profesor.menu.cuadros`)
- **ESTUDIANTE** (`student.grades.view`)

`admin.menu.admisiones` asignado a: Super Admin, Director, Secretaria, Caja, Orientador, Coordinador.

## Exports/Imports Excel (`app/Exports/`, `app/Imports/`)
- `AdmissionReportExport` — todas las columnas del formulario de admisión; respeta filtros año/estado/nivel/búsqueda; `FromQuery` + `WithMapping` + `WithStyles` + `AfterSheet` (filas alternas, autofilter, freeze)
- `AuditLogExport` — mismo patrón, filtros del componente `AuditLog`
- `ActivityTemplateExport`, `SabanaGeneralExport`, `SabanaPromedioExport`, `SabanaUnidadExport`, `StudentListExport`, `ProfessorCoursesExport`, `MissingActivitiesAdminExport`, `ActivitySummaryExport`
- **Convención:** reportes de actividades filtran a `activity_type_id=1` (normales, excluye extras)
- `ActivityScoresImport` — import con header `[ACT_ID:X]`

## PDF Helper (`app/Helpers/PDF.php`)
Extiende FPDF. Métodos clave: `CellUTF8()`, `rotatedHeader()`, `addImage()`, `dec()`. `$hideFooter = true` en cuadros. Landscape oficio: `[215, 330]`.

## Tablas de base de datos

| Grupo | Tablas |
|---|---|
| Usuarios | `users`, `students`, `professors`, `guardians`, `guardian_student`, `medical_records`, `images` |
| Académico | `levels`, `grades`, `sections`, `classrooms`, `courses`, `pensums`, `pensum_courses`, `classroom_course_assignments`, `student_enrollments` |
| Cuadros | `activity_types`, `academic_configurations`, `academic_configuration_activities`, `grade_books`, `grade_book_activities`, `grade_book_scores`, `grade_book_totals`, `grade_change_requests`, `grade_change_request_items` |
| Asistencia | `attendance_records`, `attendance_entries` |
| Admisiones | `admission_applications`, `admission_application_statuses`, `admission_application_documents`, `admission_billings`, `admission_psychometrics`, `admission_courses`, `admission_academic_scores` |
| Sistema | `audit_logs`, `system_settings`, `roles`, `permissions`, `role_has_permissions`, `cache`, `jobs`, `sessions` |

## Layout público (`resources/views/layouts/public.blade.php`)
Bootstrap 4 + FontAwesome + SweetAlert2 CDN. Clases de contenedor:
- `.public-card` (520px) — formularios pequeños
- `.public-card-wide` (760px) — formulario QR (3 tabs)
- `.admission-card` (960px) — formulario de admisiones

Componentes de página completa: `->extends('layouts.public')->section('content')` en `render()`.

## CSS personalizado
`public/css/custom.css` referenciado globalmente desde `resources/views/vendor/adminlte/page.blade.php`. Contiene: `.badge-purple`, `.badge-teal` (estados de admisión) y otros utilitarios.

## Providers y bootstrap
- `AppServiceProvider` — CarbonImmutable, alias Excel, password rules
- `FortifyServiceProvider` — actions, vistas, rate limiting
- `bootstrap/app.php` — rutas, middleware stack, alias, TokenMismatchException

## Comandos útiles
```bash
composer run dev          # serve + queue:listen + npm run dev
npm run build             # assets para producción
vendor/bin/pint --dirty   # formatear archivos modificados (OBLIGATORIO antes de commit)
php artisan migrate
php artisan db:seed
```

## Servidor de producción
- URL: `cmr.deproweb.net` | DB: `deproweb_cmr`
- Zona horaria: `America/Guatemala` | Locale: `es_GT`

## Pendientes / Deuda técnica
- **UTF-8 en correos** — workaround: editar `.env` directamente en servidor con UTF-8
- **`Grade.supervised_practice`** — columna sin usar en ningún componente; implementar o eliminar
- **`PensumCourse.is_official`** — usado en reportes pero sin badge/filtro en el CRUD de Pensums
