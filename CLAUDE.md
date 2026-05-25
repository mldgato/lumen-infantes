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
v1.8.3

## Variables de entorno clave
- `APP_NAME=Lumen` — nunca debe cambiar
- `APP_INSTITUTION_NAME="Instituto Clemente Martínez Rojas"` — nombre visible en UI
- `APP_INSTITUTION_LOGO_IMG="vendor/adminlte/dist/img/Escudo.png"`
- `REQUIRE_INSTITUTIONAL_EMAIL=false`
- `APP_LOCALE=es_GT` / `APP_FALLBACK_LOCALE=es`
- `DB_CONNECTION=mysql`
- `MAIL_MAILER=log` (desarrollo), SMTP en producción

## Convenciones de código
- Código PHP/Laravel: **inglés** (clases, métodos, variables, rutas, archivos, nombres de permisos, nombres de rutas, prefijos de URL)
- Textos de usuario: **español** (labels, validaciones, PDFs, alertas, mensajes, descripciones de permisos, textos del menú)
- **CRÍTICO — rutas y permisos siempre en inglés:** los `name` de rutas (`student.grades.index`), los prefijos de URL (`/student`), los nombres de permisos (`student.grades.view`) y los archivos de rutas (`routes/student.php`) deben usar inglés sin excepción. Los textos visibles (`'description'` de permisos, `'text'` del menú, `'header'` del menú) van en español.
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
- `routes/student.php` — todas las rutas del estudiante (prefijo `/student`)
- `routes/settings.php` — configuración de perfil/contraseña/2FA
- Middleware de permisos: `can:nombre.del.permiso`
- Middleware personalizado: `force.password.change` → `EnsurePasswordIsChanged`
- Registro de rutas y middleware en `bootstrap/app.php`

## Roles del sistema
1. Super Administrador (`$role1`)
2. Director (`$role2`)
3. Estudiante (`$role3`)
4. Profesor (`$role4`)
5. Secretaria (`$role5`)

Permisos con convención `admin.recurso.accion`, `profesor.recurso.accion` y `dashboard.panel.nombre`.
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

### Estudiante
- Ver calificaciones por unidad (solo cuadros aprobados, con indicador de riesgo)
- Ver historial de asistencia por curso con porcentaje global
- Imprimir boleta PDF personal (selecciona unidad; genera su propia boleta, nunca la de otro)
- Dashboard con KPIs: mis cursos, cursos en riesgo, % asistencia, unidades publicadas
- Perfil propio (ruta `/profile` existente en `web.php`)

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

### Períodos de inscripción y actualización
- `EnrollmentPeriod` — year, start_date, end_date, allow_enrollments:boolean, allow_data_updates:boolean
  - Métodos estáticos: `activeForEnrollments(): bool`, `activeForDataUpdates(): bool`, `hasOverlap(string $flag, string $startDate, string $endDate, ?int $excludeId): bool`
  - Regla de negocio: solo puede existir un período con `allow_enrollments=true` y un período con `allow_data_updates=true` activos simultáneamente (validado por `hasOverlap` antes de guardar).
  - Cuando `activeForEnrollments()` es false, los botones "Existente" y "Nuevo" en `EnrollmentList` quedan deshabilitados con mensaje explicativo; `enrollExisting()` y `enrollNew()` también verifican al inicio.
  - Cuando `activeForDataUpdates()` es false, `StudentDataRequest` muestra pantalla de período cerrado y bloquea `submit()`.

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
- `app/Services/GradeBookCalculationService.php` — cálculo y persistencia de totales de cuadros:
  - `recalculateAll(GradeBook, iterable $students)` — recalcula para todos los alumnos (acepta modelos o IDs)
  - `recalculateForStudents(GradeBook, iterable $studentIds)` — recalcula para IDs específicos
  - Fórmula única: `total_points = ceil(normal_points + extra_points)`
- `app/Services/AuditService.php` — métodos estáticos para registrar eventos:
  - `gradeBookStatusChanged`, `scoreChanged`
  - `enrollmentCreated`, `enrollmentStatusChanged`
  - `userCreated`, `userUpdated`
  - `gradeChangeRequestCreated`, `gradeChangeRequestResolved`
  - `configChanged`, `passwordChanged`
  - Captura IP del cliente; etiquetas en español

## Componentes Livewire (app/Livewire/)

### Dashboard (app/Livewire/Dashboard/)
Cada panel es un componente independiente protegido por su propio permiso `dashboard.panel.*`.
`dashboard.blade.php` los ensambla con `@can`/`@canany`; no hay lógica en la vista principal.

| Componente | Permiso | Roles por defecto |
|---|---|---|
| `StatsGeneral` | `dashboard.panel.stats-general` | Director, Secretaria |
| `GradeBooksPending` | `dashboard.panel.grade-books-pending` | Director |
| `StudentsByGradeChart` | `dashboard.panel.students-by-grade` | Director, Secretaria |
| `GradeBooksStatusChart` | `dashboard.panel.grade-books-status` | Director |
| `PendingChangeRequests` | `dashboard.panel.pending-change-requests` | Director |
| `LockedGradeBooks` | `dashboard.panel.locked-grade-books` | Director |
| `ProfesorStats` | `dashboard.panel.profesor-stats` | Profesor |
| `ProfesorGradeBooksChart` | `dashboard.panel.profesor-grade-books-chart` | Profesor |
| `ProfesorGradeBooksSummary` | `dashboard.panel.profesor-grade-books-summary` | Profesor |
| `ActionableGradeBooks` | `dashboard.panel.actionable-grade-books` | Profesor |
| `BirthdayStudents` | `dashboard.panel.birthday-students` | Secretaria |
| `UpcomingBirthdays` | `dashboard.panel.upcoming-birthdays` | Secretaria |
| `StudentSummary` | `dashboard.panel.student-summary` | Estudiante |

Seeder de permisos: `database/seeders/DashboardPanelPermissionsSeeder.php` (usa `firstOrCreate`, seguro de re-ejecutar).
Los paneles info-box (`StatsGeneral`, `GradeBooksPending`, `ProfesorStats`) renderizan col-divs sin `<div class="row">` propio; el row lo provee `dashboard.blade.php`.

**Patrón obligatorio para nuevos paneles:** el `<div>` raíz de cada vista de panel debe llevar `style="display: contents;"` para que el wrapper de Livewire sea transparente al layout flexbox de Bootstrap, permitiendo que los `col-*` internos participen directamente en el `<div class="row">` del `dashboard.blade.php`.
```html
<div wire:init="loadData" style="display: contents;">
    <div class="col-lg-6 mb-3">
        ...
    </div>
</div>
```

**Paneles de Profesor — null-check obligatorio:** los paneles `ProfesorGradeBooksChart`, `ProfesorGradeBooksSummary` y `ActionableGradeBooks` dependen de `Auth::user()->professor`. Siempre verificar que la relación existe antes de usarla, porque un SuperAdmin (u otro usuario sin registro en `professors`) puede tener el permiso y llegar al panel:
```php
$professor = Auth::user()->professor;

if (! $professor) {
    $this->readyToLoad = true;
    return;
}
```

### Admin (app/Livewire/Admin/)
| Componente | Responsabilidad |
|---|---|
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
| `Reports/MissingActivities` | Actividades no entregadas (tipo id=1) |
| `Reports/ActivitySummary` | Resumen pivot actividades por estudiante y materia |
| `Reports/StudentActivityDetail` | Detalle individual de actividades por alumno; filtro `filterMaxActivities` (1–10) para limitar actividades mostradas |
| `Reports/AttendanceReport` | Reporte de asistencia |
| `Reports/ProfessorCoursesExcel` | Cursos por profesor |
| `Roles/ShowRoles` | Gestión de roles (Spatie) |
| `Permissions/ShowPermissions` | Gestión de permisos |
| `Students/EnrollmentList` | Listado de inscripciones |

### Profesor (app/Livewire/Profesor/)
| Componente | Responsabilidad |
|---|---|
| `GradeBooks` | Edición completa de cuadros |
| `GradeBookGrid` | Ingreso de calificaciones en formato cuadrícula tipo Excel (Alpine.js, guardado total sin round-trips) |
| `GradeChangeRequests` | Crear solicitudes de cambio |
| `TakeAttendance` | Asistencia diaria + historial |
| `Reports/*` | Reportes específicos del profesor |

### Estudiante (app/Livewire/Estudiante/)
| Componente | Responsabilidad |
|---|---|
| `MyGrades` | Vista de calificaciones por unidad (solo lectura, cuadros aprobados) |
| `MyAttendance` | Historial de asistencia por curso con % global |
| `MyReportCard` | Selector de unidad para imprimir boleta PDF personal |

**Restricciones de seguridad del módulo Student:**
- Todos los componentes y el controlador validan que `Auth::user()->student` exista.
- Se requiere inscripción activa (`status='Activo'`) en un aula del año actual.
- El controlador `Student\ReportCardController::print()` usa el `student_id` del usuario autenticado — nunca acepta `student_id` por query string.
- Solo se muestran datos de cuadros con `status='approved'`.

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
- `MissingActivitiesAdminExport`, `MissingActivitiesProfesorExport` — actividades no entregadas (filtrado a `activity_type_id=1`)
- `ActivitySummaryExport` — tabla pivot estudiantes×materias con columnas hechas/total por curso; encabezados coloreados y leyenda de colores; `WithCustomStartCell` + `startCell()='A5'`

**Imports (app/Imports/):**
- `ActivityScoresImport` — convierte Excel a array (validación de seguridad en Livewire)

**Convención `activity_type_id=1`:** todos los reportes de actividades (`ActivitySummary`, `MissingActivities`, `StudentActivityDetail`) filtran a `activity_type_id=1` (actividades normales, excluye extras). Siempre usar `.where('activity_type_id', 1)` al consultar `$gradeBook->activities` en reportes y exports.

## PDF Helper
- `app/Helpers/PDF.php` extiende FPDF
- Métodos clave: `CellUTF8()`, `rotatedHeader()`, `addImage()`, `dec()`
- Propiedad `$hideFooter = false` — activar en cuadros de calificaciones
- Orientación landscape: `[215, 330]` (carta oficio)

## Controladores PDF de reportes
- `app/Http/Controllers/Admin/StudentActivityDetailPdfController.php`
  - `student(classroom_id, student_id, unit)` — PDF detallado por alumno (tabla actividades por curso con estado ✔/✘)
  - `classroom(classroom_id, unit)` — PDF de toda la sección (un alumno por página)
  - `studentCompact(classroom_id, student_id, unit)` — PDF resumen de una sola hoja (tabla por curso: hechas/total/faltantes, sin `SetAutoPageBreak`)
  - `classroomCompact(classroom_id, unit)` — PDF resumen de sección en oficio, 3 alumnos/hoja; llama `renderStudentCompactBlock(..., 'medium')`
  - `classroomCompactCarta(classroom_id, unit)` — PDF resumen de sección en carta, 2 alumnos/hoja; llama `renderStudentCompactBlock(..., 'large')` con fuentes más grandes
  - `buildCourseData(classroom, studentId, unit, ?int $maxActivities = null)` — método privado compartido; aplica filtro `activity_type_id=1`; acepta `max_activities` para limitar actividades mostradas
  - `renderStudentCompactBlock(string $size = 'small')` — renderiza bloque de un alumno; `$size` puede ser `'small'` (original), `'medium'` (oficio, fuentes ligeramente mayores) o `'large'` (carta, fuentes grandes aprovechando más espacio)
  - Todos los métodos públicos aceptan `?int $maxActivities` vía query string validado (`nullable|integer|min:1|max:10`)

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
├── admin/          Vistas de los componentes admin
├── profesor/       Vistas de los componentes de profesor
├── dashboard/      Vistas de los 12 paneles del dashboard (un archivo por panel)
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

### Bug / Deuda técnica
- Resolver codificación UTF-8 en correos (workaround actual: editar `.env` directamente en servidor con UTF-8)

### Prioridad alta
- **Filtrado de niveles por usuario** — ✅ Completado en v1.7.2–v1.7.5. El patrón `auth()->user()->levels()->pluck('levels.id')` se aplica en todos los componentes: `EnrollmentList`, `Classrooms`, `GradeBooks`, `ClassroomCourseAssignments`, `Students/StudentList`, `GradeChangeRequests` y todos los reportes (`MissingActivities`, `CuadrosClassroom`, `StudentListExcel`, `SabanaPromedio`, `StudentList`, `ReportCards`, `SabanaGeneral`, `SabanaUnidad`, `AttendanceReport`, `GradeProgress`).
  - **Nota:** `Users/UserList.php` usa `Level::orderBy` para los checkboxes de asignación — ese NO debe filtrarse, debe mostrar todos los niveles.
- **Componente `Admin/Professors`** — No existe gestión especializada de profesores. Crear componente con: listado filtrable, edición de datos docentes, vista de cursos asignados por año e historial de asignaciones. Actualmente los profesores solo se gestionan a través del componente genérico de usuarios.
- **Componente `Admin/Guardians`** — Los guardianes solo se administran dentro del flujo de inscripción. Crear componente independiente con búsqueda, edición y vista de todos los estudiantes relacionados con cada guardián (relación M:M con pivot `relationship_type`).
- **Campo `supervised_practice` en `Grade` — sin usar** — La columna existe en la BD y el modelo pero no se referencia en ningún componente ni vista. Implementar lógica para prácticas supervisadas o eliminar el campo.
- **Exportación del módulo de Auditoría** — `Admin/AuditLog` tiene filtros completos pero no permite exportar a Excel/PDF. Agregar exportación para reportes a directivos.

### Prioridad media
- **Campo `is_official` en `PensumCourse` — sin usar** — Existe en migración y modelo pero no aparece en ninguna vista ni filtro. Implementar badge/filtro en el componente `Admin/Pensums` o eliminar el campo.
- **Dashboard de Secretaria — accesos rápidos** — Los paneles de cumpleaños y estadísticas ya están. Agregar paneles: inscripciones recientes por estado y acceso rápido a inscripción de estudiante.
- **CRUD de `ActivityType`** — ✅ Completado en v1.7.6. Componente `Admin/ActivityTypes` con búsqueda, paginación, modal create/edit, protección de eliminación (si está en uso en config o cuadros), permiso `admin.activity-types.*`, ruta `/admin/activity-types`, menú bajo Gestión Académica.
- **Notificación: cuadro en `locked` sin revisar** — Notificar al admin cuando un cuadro lleva N días bloqueado sin ser aprobado/rechazado.
- **Notificación: cambio de rol asignado a usuario** — Notificar al usuario cuando se le asigna o revoca un rol.
- **Reporte: Estudiantes en riesgo de reprobación** — Con los datos de `GradeBookTotal` calcular qué estudiantes tienen nota < 60 en alguna unidad y generar reporte de alerta temprana para admin y profesor.
- **Reporte de asistencia con porcentaje acumulado** — El `AttendanceReport` existe pero no calcula el % de asistencia por estudiante. Agregar columna de porcentaje y umbral configurable de inasistencias.

### Prioridad baja (mejoras y reportes adicionales)
- **Servicio `GradeBookCalculationService`** — ✅ Completado en v1.7.7. `app/Services/GradeBookCalculationService.php` con `recalculateAll()` (todos los alumnos) y `recalculateForStudents()` (IDs específicos). `Profesor\GradeBooks` y `Admin\GradeChangeRequests` ya delegan al servicio.
- **Reporte: comparativo de rendimiento entre unidades** — Mostrar evolución de promedios de un aula unidad por unidad usando los datos de `GradeBookTotal`.
- **Reporte: historial de calificaciones por estudiante** — Resumen de rendimiento del estudiante a lo largo de ciclos escolares (requiere datos de múltiples años).
- **Reporte: carga docente por profesor** — Cuántos cursos, aulas y estudiantes tiene asignados cada profesor en el ciclo activo.

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
- v1.7.0 — Refactorización del dashboard: los 3 componentes monolíticos (Admin/Dashboard, Profesor/Dashboard, Secretary/Dashboard) se reemplazaron por 12 paneles independientes en `app/Livewire/Dashboard/`, cada uno con su propio permiso `dashboard.panel.*`. `dashboard.blade.php` los ensambla con `@can`; los paneles se asignan por rol desde el módulo de permisos
- v1.7.1 — Fix dashboard Profesor: null-check defensivo en `ActionableGradeBooks`, `ProfesorGradeBooksChart` y `ProfesorGradeBooksSummary` para usuarios sin registro en `professors`; fix `RoleSeeder` para no asignar paneles exclusivos de Profesor al SuperAdmin; fix `ProfessorSeeder` para crear profesores (IDs 1–13) antes que el Director y preservar el orden de IDs que otros seeders esperan
- v1.7.2 — Funcionalidad de clonar GradeBook con actividades a otros cuadros del profesor (mismo o distinto curso/unidad); relación M:M `User ↔ Level` con tabla pivote `level_user`; asignación de niveles por usuario desde `Admin/UserList`; filtrado de niveles por usuario en `EnrollmentList`
- v1.7.3 — Filtrado por nivel de usuario extendido a `Classrooms`, `GradeBooks`, `ClassroomCourseAssignments` y `Students/StudentList`: restricción de listados, cascadas y `abort(403)` en operaciones de escritura (edit/save/delete/approve/reject/manage)
- v1.7.4 — Filtrado por nivel de usuario completado en `GradeChangeRequests` (listado, openRequest/approve/reject con abort(403)) y todos los componentes de reportes admin: `MissingActivities`, `CuadrosClassroom`, `StudentListExcel`, `SabanaPromedio`, `StudentList`, `ReportCards`, `SabanaGeneral`, `SabanaUnidad` (años y niveles filtrados; abort(403) en métodos de acción)
- v1.7.5 — Filtrado por nivel de usuario extendido a `AttendanceReport` (años, niveles; abort(403) en downloadPdf) y `GradeProgress` (años, niveles, unidades; whereIn en generateReport para restringir aunque no se seleccione nivel explícito)
- v1.7.6 — CRUD `Admin/ActivityTypes`: búsqueda, paginación, modal create/edit, protección de eliminación, 4 permisos en RoleSeeder, ruta `/admin/activity-types`, entrada en menú Gestión Académica
- v1.7.7 — `GradeBookCalculationService`: centraliza cálculo de totales de cuadros; `Profesor\GradeBooks` y `Admin\GradeChangeRequests` eliminan código duplicado y delegan al servicio; footer actualizado a v1.7.7
- v1.7.8 — `EnrollmentPeriod`: modelo + migración + CRUD (`Admin/EnrollmentPeriods`) para gestionar períodos de inscripción y actualización de datos; integrado en `EnrollmentList` (botones deshabilitados + guard en `enrollExisting`/`enrollNew`) y `StudentDataRequest` (pantalla de período cerrado + guard en `submit`); menú bajo Gestión Estudiantil; 4 permisos `admin.enrollment-periods.*` asignados a SuperAdmin y Director
- v1.8.0 — Módulo Student: rutas `/student/*` en `routes/student.php`; componentes `Livewire\Student\MyGrades`, `MyAttendance`, `MyReportCard`; panel dashboard `Dashboard\StudentSummary`; controlador `Student\ReportCardController` (genera PDF de boleta personal reutilizando lógica admin); 4 permisos `student.*` + `dashboard.panel.student-summary` asignados al rol Estudiante; `StudentPermissionsSeeder` para bases existentes (también migra nombres viejos `estudiante.*`); menú `ESTUDIANTE` en `adminlte.php`
- v1.8.1 — Dos nuevos reportes de actividades admin: `Reports/ActivitySummary` (tabla pivot estudiantes×materias con hechas/total por curso, export Excel `ActivitySummaryExport` con encabezados coloreados y leyenda) + `Reports/StudentActivityDetail` (listado de alumnos con modal de detalle por curso, PDF detallado por alumno y PDF de toda la sección via `StudentActivityDetailPdfController`); menú "Actividades" con 3 ítems agrupados; seeders `ActivitySummaryPermissionSeeder` y `StudentActivityDetailPermissionSeeder` (SuperAdmin + Director)
- v1.8.2 — Filtro `activity_type_id=1` aplicado consistentemente en `ActivitySummary` (Livewire + `ActivitySummaryExport`), `MissingActivities` (Livewire + `MissingActivitiesAdminExport`) y `StudentActivityDetail` (Livewire + `buildCourseData()`); PDF compacto de una sola hoja por alumno (`studentCompact()`) en `StudentActivityDetailPdfController` con `SetAutoPageBreak(false)` y tabla por curso; ruta `admin.reports.student-activity-detail.pdf.student-compact`; botón "PDF resumen" (amarillo, `fa-compress-alt`) en vista del listado
- v1.8.3 — Filtro `filterMaxActivities` (1–10) en `Reports/StudentActivityDetail` para limitar actividades por curso en Livewire y todos los PDF (`max_activities` query param validado); nuevo PDF resumen por sección en carta con 2 alumnos/hoja (`classroomCompactCarta()`, ruta `admin.reports.student-activity-detail.pdf.classroom-compact-carta`); `renderStudentCompactBlock()` refactorizado de `bool $large` a `string $size` ('small'/'medium'/'large') para tres tiers de tamaño de fuente; nuevo componente Livewire `Profesor\GradeBookGrid` — cuadrícula tipo Excel con Alpine.js (estado cliente, guardado único vía `this.$wire.saveGrid()`), inputs `type="text" inputmode="decimal"` sin spinners, navegación Enter por columna, columnas sticky, soporte completo de mejoramiento, total en tiempo real; ruta `profesor.grade-books.grid`; botón de acceso desde `GradeBooks` por cuadro
