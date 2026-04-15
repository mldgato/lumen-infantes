# EduCheck — CLAUDE.md

## Descripción del proyecto
EduCheck es un sistema de gestión escolar desarrollado como proyecto de tesis para la
Licenciatura en Enseñanza de la Computación e Informática, EFPEM, Universidad de San
Carlos de Guatemala. Está autorizado para uso en el Instituto Clemente Martínez Rojas.

## Stack tecnológico
- PHP 8.3 / Laravel 12 / Livewire 4
- AdminLTE v3.15.3 / Bootstrap 4 (no Tailwind en vistas existentes)
- Spatie Permission v7 / Laravel Fortify v1
- FPDF (helper personalizado en `app/Helpers/PDF.php`)
- Maatwebsite Excel v3.1
- SweetAlert2 / Chart.js v4.4.0
- MySQL / Session driver: database / Queue driver: database

## Versión actual
v1.6.1

## Variables de entorno clave
- `APP_NAME=Lumen` — nunca debe cambiar
- `APP_INSTITUTION_NAME="Instituto Clemente Martínez Rojas"` — nombre visible en UI
- `APP_INSTITUTION_LOGO_IMG="vendor/adminlte/dist/img/Escudo.png"`
- `REQUIRE_INSTITUTIONAL_EMAIL=false`
- `APP_LOCALE=es_GT` / `APP_FALLBACK_LOCALE=es`
- `DB_CONNECTION=mysql`
- `MAIL_MAILER=log` (desarrollo), SMTP en producción

## Convenciones de código
- Código PHP/Laravel: **inglés** (clases, métodos, variables, rutas, archivos)
- Textos de usuario: **español** (labels, validaciones, PDFs, alertas, mensajes)
- Siempre usar `use` imports, nunca rutas inline (`\Namespace\Class`)
- Siempre llaves en estructuras de control, incluso para cuerpos de una sola línea
- PHPDoc blocks sobre comentarios inline; nunca comentarios dentro del código salvo lógica excepcionalmente compleja
- Constructor property promotion en PHP 8: `public function __construct(public Foo $foo) {}`
- Casts definidos en método `casts(): array` (no en propiedad `$casts`)
- Enums con keys en TitleCase
- Return types explícitos en todos los métodos

## Estructura de rutas
- `routes/web.php` — login, dashboard, profile, reauth
- `routes/admin.php` — todas las rutas del administrador
- `routes/profesor.php` — todas las rutas del profesor
- `routes/settings.php` — configuración de perfil/contraseña/2FA
- Middleware de permisos: `can:nombre.del.permiso`
- Middleware personalizado: `force.password.change` → `EnsurePasswordIsChanged`
- Registro de rutas y middleware en `bootstrap/app.php`

## Roles del sistema
1. Super Administrador (`$role1`)
2. Administrador (`$role2`)
3. Secretaria (`$role3`)
4. Profesor (`$role4`)
5. Estudiante (`$role5`)

Permisos con convención `admin.recurso.accion` y `profesor.recurso.accion`.
Livewire components validan con `$this->authorize('permiso')`.

## Autenticación
- **Laravel Fortify** para: login, register, reset password, 2FA (TOTP)
- **FortifyServiceProvider** configura actions, vistas y rate limiting (5 intentos/min)
- **Custom Notification:** `app/Notifications/ResetPasswordNotification.php`
- **Plantilla email reset:** `resources/views/emails/reset-password.blade.php`
- **Forced Password Change:** middleware `EnsurePasswordIsChanged` + componente Livewire
  - Redirige a `/forzar-cambio-clave` si `users.must_change_password = true`
- **Re-autenticación:** ruta `POST /reauth` para sesiones expiradas (v1.5.0)
- **Session:** database driver, lifetime 120 min

## Módulos implementados

### Administración
- Gestión de usuarios, roles y permisos (Spatie)
- Inscripciones de estudiantes (con guardianes y ficha médica)
- Cuadros de calificaciones (actividades, scores, mejoras, totales)
- Flujo de aprobación/rechazo de cuadros
- Solicitudes de cambio de notas
- Dashboard con gráficos (Chart.js)
- Reportes PDF y Excel (sábanas, boletas, cuadros, listados, actividades no entregadas)
- Auditoría general de eventos
- Configuración académica por ciclo escolar

### Profesor
- Mis Cuadros (crear actividades, calificar, bloquear, reabrir)
- Solicitar cambio de notas
- Toma de asistencia diaria con historial
- Reportes: acumulado, cuadros por unidad, cuadro vacío, listados PDF/Excel, actividades no entregadas

### Autenticación
- Login / Forgot Password / Reset Password en estilo AdminLTE
- Correo de reset con plantilla HTML personalizada
- `must_change_password` en tabla users — middleware implementado y funcional (v1.5.0)

## Modelos principales (app/Models/)

### Usuarios y personas
- `User` — autenticación + `HasRoles` (Spatie) + `TwoFactorAuthenticatable` (Fortify)
  - Fillable: cui, first_name, second_name, first_surname, second_surname, marital_status, birthdate, gender, email, password, phone, address, is_active, must_change_password
  - Relaciones: `hasOne(Student)`, `hasOne(Professor)`, `hasOne(MedicalRecord)`, `morphOne(Image)`, `hasMany(AuditLog)`
  - Accessors: `getAgeAttribute()`, `initials()`, `getFullFullNameAttribute()`
- `Student` — carne, personal_code, is_own_guardian
  - Relaciones: `belongsTo(User)`, `belongsToMany(Guardian)` con pivot `relationship_type`, `hasMany(StudentEnrollment)`, `hasMany(GradeBookScore)`, `hasMany(GradeBookTotal)`
- `Professor` — hire_date, nit, teaching_cedula, igss_affiliation, title, bachelor_degree, spouse_name, spouse_phone
  - Relaciones: `belongsTo(User)`, `hasMany(ClassroomCourseAssignment)`
- `Guardian` — datos completos del tutor
  - Relaciones: `belongsToMany(Student)` con pivot `relationship_type`
- `MedicalRecord` — medicamentos, enfermedades, alergias, cirugías, tipo_sangre, peso, altura
- `Image` — polimórfica: `morphTo()` (usada por User)

### Estructura académica
- `Level` — nivel educativo (ordering)
- `Grade` — grado (ordering, supervised_practice:boolean)
- `Section` — sección (ordering)
- `Classroom` — year + level_id + grade_id + section_id → aula única
  - Relaciones: `hasMany(StudentEnrollment)`, `hasMany(ClassroomCourseAssignment)`, `hasOneThrough(Pensum)`
- `StudentEnrollment` — student_id, classroom_id, status
- `Course` — course_name
- `Pensum` — grade_id, year, units (int), unit_percentages (array)
  - Métodos: `mainCourses()`, `getUnitPercentage(int $unit): float`
- `PensumCourse` — estructura jerárquica (parent_id), units:array, is_main, is_official, ordering
  - Relaciones: `belongsTo(self as parent)`, `hasMany(self as subCourses)`, `hasMany(ClassroomCourseAssignment)`
- `ClassroomCourseAssignment` — classroom_id, professor_id, pensum_course_id, unit (int)
  - Relaciones: `hasOne(GradeBook)`, `hasMany(AttendanceRecord)`

### Cuadros de calificaciones
- `AcademicConfiguration` — year, mode (free/assigned), improvement_type (none/full/percentage/additive), improvement_percentage
  - Métodos: `maxImprovementScore()`, `effectiveScore()`
- `AcademicConfigurationActivity` — academic_configuration_id, activity_type_id, quantity, points_each
- `ActivityType` — name, is_extra:boolean
- `GradeBook` — classroom_course_assignment_id, academic_configuration_id, status (open/locked/approved/rejected), rejection_reason
  - Métodos: `isOpen()`, `isLocked()`, `isApproved()`, `isRejected()`, `getImprovementConfig()`
- `GradeBookActivity` — grade_book_id, activity_type_id, name, max_points, ordering
- `GradeBookScore` — grade_book_activity_id, student_id, score, improvement_score (ambos decimal:2)
- `GradeBookTotal` — grade_book_id, student_id, normal_points, extra_points, total_points (todos decimal:2)
  - Total = `ceil(normal_points + extra_points)` — redondeo hacia arriba
- `GradeChangeRequest` — status (pending/approved/rejected), reviewed_by, reviewed_at
  - Métodos: `isPending()`, `isApproved()`, `isRejected()`
- `GradeChangeRequestItem` — old_score, new_score, old_improvement_score, new_improvement_score

### Asistencia y auditoría
- `AttendanceRecord` — classroom_course_assignment_id, date (1 registro = 1 día de clase)
- `AttendanceEntry` — attendance_record_id, student_id, present:boolean
- `AuditLog` — user_id, event, auditable_type, auditable_id, module, description, old_values:array, new_values:array, ip_address

## Flujo de aprobación de cuadros
1. Profesor crea el cuadro (status = **open**)
2. Crea actividades y carga calificaciones (con mejoras opcionales)
3. Valida que la suma de puntos normales = 100
4. Bloquea el cuadro (status = **locked**, auditado)
5. Admin revisa → aprueba (status = **approved**) o rechaza con motivo (status = **rejected**)
6. Si rechazado, profesor puede reabrir (status = **open**) y editar

## Servicios
- `app/Services/AuditService.php` — métodos estáticos para registrar eventos:
  - `gradeBookStatusChanged`, `scoreChanged`
  - `enrollmentCreated`, `enrollmentStatusChanged`
  - `userCreated`, `userUpdated`
  - `gradeChangeRequestCreated`, `gradeChangeRequestResolved`
  - `configChanged`, `passwordChanged`
  - Captura IP del cliente; etiquetas en español

## Componentes Livewire (app/Livewire/)

### Admin (app/Livewire/Admin/)
| Componente | Responsabilidad |
|---|---|
| `Dashboard` | Estadísticas + gráficos Chart.js |
| `Levels`, `Grades`, `Sections` | CRUD con paginación y búsqueda |
| `Classrooms` | CRUD de aulas |
| `Courses`, `Pensums` | Gestión de cursos y planes |
| `ClassroomCourseAssignments` | Asignación profesor-curso |
| `AcademicConfigurations` | Config por ciclo escolar |
| `GradeBooks` | Lista filtrable + aprobación/rechazo |
| `GradeChangeRequests` | Gestión de solicitudes |
| `AuditLog` | Registro de auditoría |
| `Reports/SabanaGeneral` | Sábana general |
| `Reports/SabanaPromedio` | Sábana de promedios |
| `Reports/SabanaUnidad` | Sábana por unidad |
| `Reports/CuadrosClassroom` | Cuadros por aula |
| `Reports/ReportCards` | Boletas PDF |
| `Reports/StudentList` | Listado de estudiantes |
| `Reports/StudentListExcel` | Export Excel de estudiantes |
| `Reports/MissingActivities` | Actividades no entregadas |
| `Reports/AttendanceReport` | Reporte de asistencia |
| `Reports/ProfessorCoursesExcel` | Cursos por profesor |
| `Roles/ShowRoles` | Gestión de roles (Spatie) |
| `Permissions/ShowPermissions` | Gestión de permisos |
| `Students/EnrollmentList` | Listado de inscripciones |

### Profesor (app/Livewire/Profesor/)
| Componente | Responsabilidad |
|---|---|
| `Dashboard` | Estadísticas + cuadros accionables |
| `GradeBooks` | Edición completa de cuadros |
| `GradeChangeRequests` | Crear solicitudes de cambio |
| `TakeAttendance` | Asistencia diaria + historial |
| `Reports/*` | Reportes específicos del profesor |

### Forms (app/Livewire/Forms/) — objetos `Livewire\Form`
`UserForm`, `StudentForm`, `GuardianForm`, `ProfessorForm`, `MedicalForm`,
`LevelForm`, `SectionForm`, `ClassroomForm`, `CourseForm`, `GradeForm`,
`PensumForm`, `AcademicConfigurationForm`

### Settings y Profile
`Settings\Profile`, `Settings\Password`, `Settings\Appearance`, `Settings\TwoFactor`,
`Profile\UpdateProfile`, `Profile\UpdateProfessorInfo`, `Profile\UpdateMedicalInfo`

## Creación de componentes Livewire

**CRÍTICO:** Siempre usar `--class` al crear componentes Livewire:
```bash
php artisan make:livewire NombreComponente --class
# También funciona con subdirectorios:
php artisan make:livewire Admin/NombreComponente --class
```
Sin `--class`, artisan crea un componente Volt anónimo (vista con ⚡ prefix) en lugar de una clase PHP + vista separada.

## Patrones Livewire establecidos

### Cascading dropdowns (patrón Admin)
```php
public function updatedFilterYear(): void
{
    $this->reset(['filterLevel', 'filterGrade', ...]);
}
// En render(): cargar opciones dinámicamente según filtros activos
```

### Query string para persistencia de filtros
```php
protected $queryString = ['search', 'filterYear', 'cant'];
```

### Reset de paginación al filtrar
```php
public function updatingSearch(): void { $this->resetPage(); }
public function updatingCant(): void { $this->resetPage(); }
```

### Ordenamiento dinámico
Clic en encabezado alterna `$sortDirection` entre asc/desc sobre `$sortField`.

### Dispatch de eventos hacia Alpine/JS
```php
$this->dispatch('closeModalMessaje', title: '...', text: '...', icon: 'success');
$this->dispatch('showAlert', title: '...', icon: 'success');
$this->dispatch('toastMessage', title: '...', icon: 'success');
```

### Transacciones DB en operaciones críticas
```php
DB::transaction(function () {
    // Excel import, asistencia, cuadros
});
```

### Inputs de búsqueda (buscadores)
Todos los `<input>` usados como buscador deben llevar `autocomplete="new-password"`.
`autocomplete="off"` es ignorado por Chrome; `new-password` le indica al navegador que
no debe rellenar el campo con credenciales guardadas.

Contexto: en componentes sin `type="email"` + `type="password"` en el mismo DOM,
Chrome rellena el primer `<input type="text">` con el correo del usuario logueado.
```html
<input type="text" wire:model.live.debounce.300ms="search" class="form-control"
    placeholder="Buscar..." autocomplete="new-password">
```

## Eventos de Livewire usados
- `closeModalMessaje` — cierra modal y muestra SweetAlert
- `showAlert` — SweetAlert top-end sin modal
- `toastMessage` — toast top-end
- `openEnrollmentModal`, `openAuditDetailModal` — abrir modales específicos
- `downloadAttendancePdf` — trigger descarga PDF asistencia

## Exportaciones e importaciones Excel
**Exports (app/Exports/):**
- `ActivityTemplateExport` — plantilla formateada con header `[ACT_ID:X]` para cargar notas
- `SabanaGeneralExport`, `SabanaPromedioExport`, `SabanaUnidadExport`
- `StudentListExport`, `ProfessorCoursesExport`
- `MissingActivitiesAdminExport`, `MissingActivitiesProfesorExport`

**Imports (app/Imports/):**
- `ActivityScoresImport` — convierte Excel a array (validación de seguridad en Livewire)

## PDF Helper
- `app/Helpers/PDF.php` extiende FPDF
- Métodos clave: `CellUTF8()`, `rotatedHeader()`, `addImage()`, `dec()`
- Propiedad `$hideFooter = false` — activar en cuadros de calificaciones
- Orientación landscape: `[215, 330]` (carta oficio)

## Patrón de datos postgrado
- Secciones con `level_id` 2 o 5 usan `Enrollment.carne` para display/sorting
- Otras secciones usan `User.carne`
- Patrón de ordenamiento: `leftJoin` + `orderByRaw('CAST(COALESCE(...) AS UNSIGNED) ASC')`

## Tablas de base de datos (43 migraciones)

| Tabla | Descripción |
|---|---|
| `users` | Autenticación + 2FA + must_change_password |
| `students` | Datos académicos del estudiante |
| `professors` | Datos laborales del profesor |
| `guardians` | Tutores legales |
| `guardian_student` | Pivot M:M con relationship_type |
| `medical_records` | Fichas médicas |
| `images` | Polimórfica para avatares |
| `levels`, `grades`, `sections` | Estructura académica |
| `classrooms` | Aula = nivel + grado + sección + año |
| `courses`, `pensums`, `pensum_courses` | Plan de estudios jerárquico |
| `classroom_course_assignments` | Profesor ↔ curso ↔ aula |
| `student_enrollments` | Inscripciones con status |
| `activity_types` | Tipos (is_extra distingue normales/extras) |
| `academic_configurations` | Config por ciclo (mode, improvement_type) |
| `academic_configuration_activities` | Actividades predefinidas |
| `grade_books` | Cuadro con status workflow |
| `grade_book_activities` | Actividades dentro del cuadro |
| `grade_book_scores` | score + improvement_score por estudiante |
| `grade_book_totals` | normal_points + extra_points + total_points |
| `grade_change_requests` | Solicitudes con reviewed_by/at |
| `grade_change_request_items` | Valores old/new por actividad |
| `attendance_records` | 1 registro = 1 día de clase |
| `attendance_entries` | present:boolean por estudiante |
| `audit_logs` | Auditoría completa con old/new values |
| `roles`, `permissions`, `role_has_permissions` | Spatie |
| `cache`, `jobs`, `sessions` | Laravel estándar |

## Estructura de vistas (resources/views/)
```
livewire/
├── admin/          Vistas de los 23 componentes admin
├── profesor/       Vistas de los componentes de profesor
├── auth/           Login, register, 2FA, reset-password
├── settings/       Perfil, contraseña, apariencia, 2FA
├── profile/        UpdateProfile, UpdateProfessorInfo, UpdateMedicalInfo
└── force-password-change.blade.php
emails/
└── reset-password.blade.php   Plantilla HTML de reset
components/         Componentes Blade reutilizables
admin/              Vistas de reportes y config (no Livewire)
profesor/           Vistas de reportes profesor
```

## Providers y bootstrap
- `AppServiceProvider` — CarbonImmutable, alias Excel, password rules por env
- `FortifyServiceProvider` — actions, vistas, rate limiting (5/min login y 2FA)
- `bootstrap/providers.php` — registra AppServiceProvider, FortifyServiceProvider, ExcelServiceProvider
- `bootstrap/app.php` — rutas, middleware stack, alias, manejo TokenMismatchException

## Comandos útiles
```bash
# Desarrollo
composer run dev         # serve + queue:listen + npm run dev (concurrentemente)
npm run build            # build assets para producción

# Calidad de código
vendor/bin/pint --dirty  # formatear solo archivos modificados (OBLIGATORIO antes de commit)

# Tests
php artisan test --compact                      # todos los tests
php artisan test --compact --filter=NombreTest  # filtrar tests

# Base de datos
php artisan migrate
php artisan db:seed

# Setup inicial
composer run setup       # instala deps, genera .env, crea DB
```

## Servidor de producción
- URL: `cmr.deproweb.net`
- Base de datos: `deproweb_cmr`
- Zona horaria: `America/Guatemala`
- Locale: `es_GT`

## Sistema de actualización de datos via enlace público

### Flujo implementado (Prompt 1)
1. Alumno accede a `GET /actualizar-datos` (ruta pública, sin auth)
2. Ingresa su código personal o carné + correo electrónico
3. Sistema verifica si ya actualizó datos este año (`student_data_updates.year`)
4. Si no actualizó: genera token (60 chars), lo guarda en cache `"student_update_{token}"` por 60 min con `[student_id, email_nuevo]`, y envía `StudentDataUpdateNotification`
5. Alumno hace clic en enlace del correo → `GET /actualizar-datos/{token}`
6. `StudentDataController::verifyToken()` valida el token en cache → muestra formulario o vista de expirado

### Componentes
- `App\Livewire\StudentDataRequest` — Componente público (Paso 1)
- `App\Http\Controllers\StudentDataController` — Validación de token
- `App\Notifications\StudentDataUpdateNotification` — Email con enlace
- `App\Models\StudentDataUpdate` — Registro de actualizaciones completadas
- `resources/views/student-data/expired.blade.php` — Enlace expirado
- `resources/views/student-data/form.blade.php` — Placeholder para Prompt 2
- `resources/views/layouts/public.blade.php` — Layout sin auth para páginas públicas

### El formulario completo (Prompt 2) debe:
- Recibir `$token`, `$studentId`, `$emailNuevo` desde la vista `student-data/form.blade.php`
- Cargar y editar datos del User, Student, Guardian(es), MedicalRecord
- Al guardar: crear registro en `student_data_updates` con `completed_at = now()`, `ip_address`, limpiar el token del cache
- Considerar que `email_nuevo` puede diferir del `user.email` actual

## Sistema de actualización de datos — Formulario (Prompt 2)

### Flujo implementado
1. `StudentDataController::verifyToken()` valida token → `student-data/form.blade.php`
2. `form.blade.php` extiende `layouts.public` e incrusta `<livewire:student-data-update-form :token="$token" />`
3. `StudentDataUpdateForm` (app/Livewire/) carga datos del alumno en `mount()`, presenta 3 tabs Bootstrap 4
4. `save()` ejecuta DB transaction: actualiza User, crea/actualiza MedicalRecord y Guardian, crea StudentDataUpdate, borra token del cache, hace audit log (módulo: 'Actualización QR'), redirige a `student.data.done`
5. `student-data/done.blade.php` — página estática de confirmación (sin Livewire)

### Rutas públicas
```
GET /actualizar-datos           → StudentDataRequest (Livewire)
GET /actualizar-datos/completado → student-data.done (Route::view)
GET /actualizar-datos/{token}   → StudentDataController::verifyToken
```
**IMPORTANTE:** la ruta `/completado` debe ir ANTES de `{token}` para no ser capturada como token.

### Layout public
- `resources/views/layouts/public.blade.php` — layout sin auth con Bootstrap 4 + FontAwesome CDN
- `.public-card` (max-width: 520px) — para formularios pequeños
- `.public-card-wide` (max-width: 760px) — para el formulario de 3 tabs

## Pendientes
- Resolver codificación UTF-8 en correos (workaround actual: editar `.env` directamente en servidor con UTF-8)

## Historial de versiones
- v1.0.0 — Base del sistema
- v1.1.0 — Reportes PDF y Excel
- v1.2.0 — Solicitudes de cambio de notas y dashboards
- v1.3.0 — Inscripciones de estudiantes
- v1.4.0 — Auditoría general y reorganización del menú
- v1.4.1 — Correcciones PDF y validaciones
- v1.4.2 — Fix autenticación y correo reset
- v1.4.3 — Optimización integral de procesos académicos, reportes y auditoría
- v1.5.0 — Modal de re-autenticación por sesión expirada, asistencia y cambio forzado de contraseña
- v1.6.0 — Audit logging en componentes de perfil (UpdateProfile, UpdateProfessorInfo, UpdateMedicalInfo) + sistema de actualización de datos para estudiantes via QR (formulario público, notificación por correo, token con expiración, registro único por año)
- v1.6.1 — Fix autorrelleno del navegador en buscadores: `autocomplete="new-password"` en los 19 inputs de búsqueda
