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
v1.9.9

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
6. Caja (`$role6`)

Permisos con convención `admin.recurso.accion`, `profesor.recurso.accion` y `dashboard.panel.nombre`.
Livewire components validan con `$this->authorize('permiso')`.

## Autenticación
- **Laravel Fortify** para: login, register, reset password, 2FA (TOTP)
- **FortifyServiceProvider** configura actions, vistas y rate limiting (5 intentos/min)
- **Custom Notification:** `app/Notifications/ResetPasswordNotification.php`
- **Plantilla email reset:** `resources/views/emails/reset-password.blade.php`
- **Forced Password Change:** middleware `EnsurePasswordIsChanged` + componente Livewire
  - Redirige a `/forzar-cambio-clave` si `users.must_change_password = true`
- **Re-autenticación:** ruta `POST /reauth` para sesiones expiradas
- **Session:** database driver, lifetime 120 min

## Módulos implementados

### Estudiante
- Ver calificaciones por unidad (solo cuadros aprobados, con indicador de riesgo)
- Ver historial de asistencia por curso con porcentaje global
- Imprimir boleta PDF personal (selecciona unidad; genera su propia boleta, nunca la de otro)
- Dashboard con KPIs: mis cursos, cursos en riesgo, % asistencia, unidades publicadas
- Perfil propio (ruta `/profile` existente en `web.php`)

### Módulo de Admisiones
- **Formulario público** en `/admisiones` (sin auth): 7 secciones — datos del alumno, grado, padre (toggle), madre (toggle), encargado, familia e hijos, cómo nos conoció
- **Configuración** en `/admin/settings`: modo de inscripción (`direct` o `admissions`); en modo `admissions` el formulario público acepta solicitudes
- **Panel admin** en `/admin/students/admissions`: listado filtrable, modal de detalle completo, gestión de papelería con checkboxes
- **Flujo de estados**: `pending` → `emailed` (correo enviado, manual) → `reviewed` (documentación completa, automático) → flujo de evaluación/facturación (pendiente) → `accepted`/`rejected`
- **Papelería completa** = 5 checkboxes marcados **Y** ambas URLs (`url_documents` + `url_payment`) guardadas; solo entonces el estado cambia a `reviewed`. Se puede guardar parcialmente sin que cambie el estado.
- **`syncDocumentStatus()`** (método privado en `AdmissionList`): evalúa completitud real (checkboxes + URLs) y transiciona entre `emailed` ↔ `reviewed`; se llama desde `toggleDocument()` y `updateApplication()`.
- **Papelería bloqueada en `pending`**: checkboxes deshabilitados con aviso explicativo hasta que el estado sea al menos `emailed`; guard en `toggleDocument()` en el servidor
- **Botón Rechazar**: visible **solo** en estado `pending` (tabla + footer del modal); desaparece al cambiar de estado
- **Botón Aceptar**: removido temporalmente; aparecerá cuando todos los estados del flujo de admisión estén completos (implementación pendiente)
- **Handlers de eventos** (`showAlert`, `toastMessage`): definidos por componente en el bloque `@script` con `$wire.on()`; no son globales. Dispatch con array `['title' => '...', 'type' => 'success']`
- **Tabs del modal con Alpine.js**: las 4 pestañas usan `x-data="{ activeTab }"` + `x-show` en lugar de `data-toggle="tab"` de Bootstrap, preservando la pestaña activa durante re-renders de Livewire
- **Confirmaciones SweetAlert2**: botones de acción usan `Swal.fire()` vía Alpine en lugar de `wire:confirm`
- **NIT del encargado** solo se almacena en `guardian_nit` cuando el tipo es "Otro"; para padre/madre se usa su propio campo `father_nit`/`mother_nit`
- **Interruptores padre/madre**: si se desactivan, sus datos quedan nulos; la opción de encargado se oculta dinámicamente
- **Ciclo escolar del formulario**: enero–junio muestra año actual + siguiente; julio–diciembre solo el siguiente año
- **Vista de Facturación** en `/admin/students/admissions/billing`: listado filtrable con nombre del encargado y NIT según tipo (`guardianNit()`); botón de factura deshabilitado si la solicitud no tiene `url_payment`; modal de dos estados: formulario (sin factura) o detalle de solo lectura (con factura); datos de factura en tabla `admission_billings`

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
  - Regla de negocio: solo puede existir un período activo por cada flag simultáneamente (validado por `hasOverlap` antes de guardar).
  - Cuando `activeForEnrollments()` es false, los botones "Existente" y "Nuevo" en `EnrollmentList` quedan deshabilitados; `enrollExisting()` y `enrollNew()` también verifican al inicio.
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
  - `gradeScoresCopied(targetGradeBook, oldScores, newScores, originCourseName, originUnit)` — registra copia de notas entre cuadros; `old_values`/`new_values` contienen snapshot `[alumno => [actividad => nota]]`
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

**Paneles de Profesor — null-check obligatorio:** los paneles `ProfesorGradeBooksChart`, `ProfesorGradeBooksSummary` y `ActionableGradeBooks` dependen de `Auth::user()->professor`. Siempre verificar que la relación existe antes de usarla, porque un SuperAdmin puede tener el permiso y llegar al panel:
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
| `ClassroomCourseAssignments` | Asignación profesor-curso; soporta reemplazo de profesor en los tres escenarios (sin cuadro, cuadro vacío, cuadro con actividades/calificaciones); si el cuadro estaba `rejected` al transferir, se reabre automáticamente |
| `AcademicConfigurations` | Config por ciclo escolar |
| `GradeBooks` | Lista filtrable + aprobación/rechazo |
| `GradeChangeRequests` | Gestión de solicitudes |
| `AuditLog` | Registro de auditoría; la vista del modal de detalle maneja `old_values`/`new_values` arrays anidados (ej. snapshot de notas) renderizándolos como mini-tablas legibles |
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
| `Reports/AttendanceReport` | Reporte de asistencia con toggle Sesiones/Resumen y % por alumno |
| `Reports/StudentsAtRisk` | Estudiantes con promedio ponderado < umbral (default 60%) |
| `Reports/GradeProgressComparison` | Promedio por unidad por curso en un aula |
| `Reports/StudentHistory` | Historial multi-año de calificaciones ponderadas por alumno |
| `Reports/ProfessorWorkload` | Carga docente: asignaciones, aulas y alumnos por profesor |
| `Reports/ProfessorCoursesExcel` | Cursos por profesor |
| `Professors` | Listado filtrable de profesores con edición de datos laborales y detalle de cursos |
| `Guardians` | Listado de guardianes con edición y vista de estudiantes relacionados |
| `Roles/ShowRoles` | Gestión de roles (Spatie) |
| `Permissions/ShowPermissions` | Gestión de permisos |
| `Students/EnrollmentList` | Listado de inscripciones |
| `Students/AdmissionList` | Listado y gestión de solicitudes de admisión; papelería completa = 5 checkboxes + ambas URLs; `syncDocumentStatus()` evalúa y transiciona `emailed`↔`reviewed`; Rechazar solo en `pending`; Aceptar removido (condiciones pendientes) |
| `Students/AdmissionBillingList` | Facturación de admisiones: tabla con encargado + NIT por tipo (`guardianNit()`); modal de dos estados: formulario (invoice_number + invoice_date + link a boleta) cuando no hay factura, o detalle de solo lectura cuando ya está registrada; modal solo abre en estado `reviewed`; botón deshabilitado sin `url_payment` |
| `Students/AdmissionPsychometricList` | Evaluación psicométrica: modal solo abre en estado `billed`; TAB Psicométrica editable (editor Quill) en estado `billed`, solo lectura en `psychometric`/`accepted`/`rejected`; transiciona a `psychometric` al guardar por primera vez; filtro Nivel restringido a niveles del usuario |
| `Students/AdmissionAcademicList` | Evaluaciones académicas: modal editable solo en estado `psychometric` (agregar/eliminar punteos, botón "Finalizar" con SweetAlert transiciona a `academic`); modal de solo lectura (datos del alumno + punteos + promedio) en cualquier otro estado que tenga punteos registrados; filtro Nivel restringido a niveles del usuario; permiso `admin.admissions.academic` (Coordinador + Super Admin) |
| `Students/StudentSelector` | Copia de calificaciones entre cuadros (curso/unidad origen → destino); selección individual o masiva de alumnos; dos caminos: directo (reemplaza actividades completas) y mapeo manual (selección parcial con destino que ya tiene actividades); cuadro destino queda en `approved` al finalizar; registra auditoría con snapshot antes/después vía `AuditService::gradeScoresCopied` |
| `Admin/AdmissionCourses` | CRUD de materias de admisión (`AdmissionCourse`); campos: nombre + orden; permiso `admin.admission-courses.index` (Secretaria + Super Admin) |
| `Admin/SystemSettings` | Configuraciones globales del sistema (`enrollment_mode`: direct/admissions) |

### Profesor (app/Livewire/Profesor/)
| Componente | Responsabilidad |
|---|---|
| `GradeBooks` | Edición completa de cuadros |
| `GradeBookGrid` | Ingreso de calificaciones en formato cuadrícula tipo Excel (Alpine.js, guardado total sin round-trips); incluye Bloquear Cuadro, Reabrir, Clonar actividades a otra sección (requiere actividades normales = 100 pts) y descarga de PDF cuando el cuadro está aprobado. **Importante:** en `render()`, el lookup de scores usa un mapa indexado con `(int)` explícito — nunca usar `===` para comparar IDs de Eloquent con campos de colecciones crudas (PDO puede devolver strings en producción) |
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
- `AuditLogExport` — exportación Excel del registro de auditoría con los mismos filtros activos del componente `Admin/AuditLog`

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
  - `classroomCompactCarta(classroom_id, unit)` — PDF resumen de sección en carta, 2 alumnos/hoja; llama `renderStudentCompactBlock(..., 'large')`
  - `buildCourseData(classroom, studentId, unit, ?int $maxActivities = null)` — método privado compartido; aplica filtro `activity_type_id=1`; acepta `max_activities` para limitar actividades mostradas
  - `renderStudentCompactBlock(string $size = 'small')` — `$size` puede ser `'small'` (original), `'medium'` (oficio), o `'large'` (carta, fuentes grandes)
  - Todos los métodos públicos aceptan `?int $maxActivities` vía query string validado (`nullable|integer|min:1|max:10`)

## Patrón de datos postgrado
- Secciones con `level_id` 2 o 5 usan `Enrollment.carne` para display/sorting
- Otras secciones usan `User.carne`
- Patrón de ordenamiento: `leftJoin` + `orderByRaw('CAST(COALESCE(...) AS UNSIGNED) ASC')`

## Tablas de base de datos (49 migraciones)

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
| `system_settings` | Configuraciones globales clave-valor (ej. `enrollment_mode`) |
| `admission_applications` | Solicitudes de admisión con datos del alumno, padres, encargado y familia |
| `admission_application_statuses` | Historial de cambios de estado por solicitud (user_id, notes) |
| `admission_application_documents` | Papelería por solicitud: payment_receipt, grades_certificate, registration_form, reference_letter, photo |
| `admission_billings` | Factura por solicitud: invoice_number, invoice_date, user_id; relación 1:1 con admission_applications |
| `admission_psychometrics` | Evaluación psicométrica por solicitud: result, notes (HTML), user_id; relación 1:1 con admission_applications |
| `admission_courses` | Catálogo de materias de admisión: name, ordering |
| `admission_academic_scores` | Punteo por solicitud y materia: admission_application_id, admission_course_id, score decimal(5,2), user_id; unique `aas_application_course_unique` |

## Estructura de vistas (resources/views/)
```
livewire/
├── admin/          Vistas de los componentes admin
├── profesor/       Vistas de los componentes de profesor
├── dashboard/      Vistas de los 13 paneles del dashboard (un archivo por panel)
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

## Sistema de actualización de datos (QR)

### Flujo completo
1. Alumno accede a `GET /actualizar-datos` (ruta pública, sin auth): ingresa código personal o carné + correo
2. Sistema verifica si ya actualizó datos este año (`student_data_updates.year`)
3. Si no actualizó: genera token (60 chars), lo guarda en cache `"student_update_{token}"` por 60 min con `[student_id, email_nuevo]`, y envía `StudentDataUpdateNotification`
4. Alumno hace clic en enlace del correo → `GET /actualizar-datos/{token}`
5. `StudentDataController::verifyToken()` valida el token → muestra formulario o vista de expirado
6. `StudentDataUpdateForm` (app/Livewire/) carga datos del alumno en `mount()`, presenta 3 tabs Bootstrap 4
7. `save()` ejecuta DB transaction: actualiza User, crea/actualiza MedicalRecord y Guardian, crea `StudentDataUpdate` con `completed_at=now()` e `ip_address`, borra token del cache, audit log (módulo: 'Actualización QR'), redirige a `student.data.done`

### Rutas públicas
```
GET /actualizar-datos            → StudentDataRequest (Livewire)
GET /actualizar-datos/completado → student-data.done (Route::view)  ← debe ir ANTES de {token}
GET /actualizar-datos/{token}    → StudentDataController::verifyToken
```

### Componentes clave
- `App\Livewire\StudentDataRequest` — paso 1 (verificación de identidad)
- `App\Livewire\StudentDataUpdateForm` — formulario completo de edición
- `App\Http\Controllers\StudentDataController` — validación de token
- `App\Notifications\StudentDataUpdateNotification` — email con enlace
- `App\Models\StudentDataUpdate` — registro de actualizaciones completadas

### Layout public
- `resources/views/layouts/public.blade.php` — layout sin auth con Bootstrap 4 + FontAwesome + SweetAlert2 CDN; soporta `@stack('styles')` y `@stack('scripts')`
- `.public-card` (max-width: 520px) — formularios pequeños
- `.public-card-wide` (max-width: 760px) — formulario de 3 tabs
- `.admission-card` (max-width: 960px) — formulario de admisiones (7 secciones)
- Componentes de página completa deben usar `->extends('layouts.public')->section('content')` en `render()`

## Pendientes

### Bug / Deuda técnica
- Resolver codificación UTF-8 en correos (workaround actual: editar `.env` directamente en servidor con UTF-8)
- **Campo `supervised_practice` en `Grade` — sin usar** — La columna existe en la BD y el modelo pero no se referencia en ningún componente ni vista. Implementar lógica para prácticas supervisadas o eliminar el campo.
- **Campo `is_official` en `PensumCourse`** — Usado en `StudentsAtRisk`, `GradeProgressComparison`, `StudentHistory` y `StudentSummary`. Agregar badge/filtro visual en el CRUD de `Admin/Pensums` si se desea.

### Funcionalidad pendiente — Admisiones
- **Botón Aceptar en `AdmissionList`** — Removido temporalmente. Debe reaparecer una vez que la solicitud haya completado todo el flujo de admisión: `reviewed` → `billed` → `psychometric` → `academic`. La condición exacta para habilitarlo es que `current_status === 'academic'`. Implementación pendiente.

## Historial de versiones
- v1.0.0–v1.8.5 — Base del sistema, autenticación Fortify + 2FA, reportes PDF/Excel, inscripciones, auditoría, dashboard por paneles independientes, módulo Estudiante, GradeBookGrid tipo Excel, sistema de actualización de datos QR, admisiones (formulario público + panel admin)
- v1.9.0 — `Admin/Professors`, `Admin/Guardians`, `AuditLogExport`, notificaciones `RoleAssigned`/`RoleRevoked`, panel Secretaria inscripciones recientes, `AttendanceReport` Sesiones/Resumen, `StudentsAtRisk`, comando `gradebooks:notify-stale`, `GradeProgressComparison`, `StudentHistory`, `ProfessorWorkload`
- v1.9.1 — Módulo de Admisiones completo: formulario público `/admisiones` (7 secciones), `SystemSetting` key-value, panel `AdmissionList`, flujo `pending→emailed→reviewed→accepted/rejected`, 3 permisos nuevos
- v1.9.2 — Fix `AdmissionList`: tabs Alpine.js (preservan pestaña activa), confirmaciones SweetAlert2, papelería bloqueada en estado `pending`
- v1.9.3 — `StudentSelector`: cuadro destino queda `approved` al copiar notas; selección parcial permitida sobre cuadros `approved`; auditoría con snapshot `[alumno → actividad → nota]` antes/después; `AuditService::gradeScoresCopied`; vista `AuditLog` renderiza arrays anidados como mini-tablas
- v1.9.4 — fix `GradeBookGrid`: lookup de notas usaba `===` (estricto) causando que el driver PDO en producción (strings) no coincidiera con IDs Eloquent (int); reemplazado por mapa indexado con cast explícito `(int)`
- v1.9.5 — `AdmissionList`: papelería completa requiere 5 checkboxes + ambas URLs; `syncDocumentStatus()` como método privado reutilizable; Rechazar solo en `pending`; Aceptar removido; handlers `showAlert`/`toastMessage` por componente. Nuevo rol `Caja`. Nuevo módulo `AdmissionBillingList`: tabla de facturación, modelo `AdmissionBilling`, tabla `admission_billings`, métodos `guardianNit()` y `fullStudentName()` en `AdmissionApplication`
- v1.9.6 — Flujo de admisiones extendido: nuevos estados `billed` y `psychometric` en `AdmissionApplicationStatus`; `AdmissionBillingList::saveBilling()` transiciona a `billed` y registra historial; botón "Regresar a Pendiente" bloqueado desde `reviewed` en adelante (vista + guard servidor); nuevo módulo `AdmissionPsychometricList` con modelo `AdmissionPsychometric`, tabla `admission_psychometrics`, editor Quill (títulos/color/alineación), modal de 5 tabs solo lectura + pestaña Psicométrica editable; permiso `admin.admissions.psychometric` asignado a roles Director y Super Administrador; nuevo rol `Orientador` en seeder
- v1.9.7 — fix `AdmissionPsychometricList`: modal se cierra automáticamente al guardar evaluación (`dispatch('closePsychometricModal')` + handler JS `modal('hide')`); TAB Psicométrica queda en solo lectura cuando `current_status` es `psychometric`, `accepted` o `rejected` (muestra resultado como badge e HTML de anotaciones sin editor Quill); guard en `initPsychometricQuill` evita error si el editor no está en el DOM
- v1.9.8 — filtro Nivel (Level) agregado a `AdmissionList` y `AdmissionPsychometricList` (filtro por `level_id`, con `allLevels` Computed en ambos); distribución de cols de filtros ajustada a 2/2/3/3/2; modal de `AdmissionList` agrega tabs condicionales: Facturación (si `billing` existe) y Psicométrica (si `psychometric` existe), ambas en solo lectura; `viewApplication()` carga relaciones `billing.user` y `psychometric.user`
- v1.9.9 — nuevo estado `academic` en flujo de admisiones; nuevo módulo `AdmissionAcademicList` (permiso `admin.admissions.academic`, rol Coordinador + Super Admin): modal editable en estado `psychometric` (agregar/eliminar punteos, finalizar con SweetAlert) y de solo lectura en estados posteriores; nuevo CRUD `AdmissionCourses` (permiso `admin.admission-courses.index`, rol Secretaria + Super Admin) con 6 materias seeder (Lenguaje, Destrezas, Matemáticas, Inglés, Educación Católica, Tecnología); modelos `AdmissionCourse` y `AdmissionAcademicScore`, tablas `admission_courses` y `admission_academic_scores` (unique `aas_application_course_unique`); filtro Nivel restringido en los 3 módulos de admisiones; modal `AdmissionList` agrega tabs condicionales Académico, Facturación y Psicométrica; select de estado incluye `academic` en todos los módulos; menú y rutas para ambos componentes; rol `Coordinador` en seeder; botón Aceptar pendiente (condición: `current_status === 'academic'`)
