<div wire:init="loadData">

    {{-- Enlace de actualización de datos para compartir --}}
    <div class="alert alert-light border mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2"
        role="alert">
        <span class="text-sm text-muted">
            <i class="fas fa-link mr-1"></i>
            URL pública para actualización de datos de estudiantes:
            <code class="ml-1">{{ route('student.data.request') }}</code>
        </span>
        <a href="{{ route('student.data.request') }}" target="_blank" class="btn btn-sm btn-outline-success">
            <i class="fas fa-external-link-alt mr-1"></i> Abrir
        </a>
    </div>

    {{-- FILTROS --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold"><i class="fas fa-filter mr-1"></i> Seleccionar Aula</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-2 form-group mb-0">
                    <label class="text-sm mb-1">Año <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-calendar-alt"></i></span></div>
                        <select wire:model.live="filterYear" class="form-control">
                            <option value="">-- Año --</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 form-group mb-0">
                    <label class="text-sm mb-1">Nivel <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-layer-group"></i></span></div>
                        <select wire:model.live="filterLevel" class="form-control" {{ !$filterYear ? 'disabled' : '' }}>
                            <option value="">-- Nivel --</option>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 form-group mb-0">
                    <label class="text-sm mb-1">Grado <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-graduation-cap"></i></span></div>
                        <select wire:model.live="filterGrade" class="form-control"
                            {{ !$filterLevel ? 'disabled' : '' }}>
                            <option value="">-- Grado --</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 form-group mb-0">
                    <label class="text-sm mb-1">Sección <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-door-open"></i></span></div>
                        <select wire:model.live="filterSection" class="form-control"
                            {{ !$filterGrade ? 'disabled' : '' }}>
                            <option value="">-- Sección --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 form-group mb-0">
                    @if ($classroom)
                        <button wire:click="openModal('existing')" class="btn btn-success btn-sm shadow-sm mr-1">
                            <i class="fas fa-user-check"></i> Existente
                        </button>
                        <button wire:click="openModal('new')" class="btn btn-primary btn-sm shadow-sm">
                            <i class="fas fa-user-plus"></i> Nuevo
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- RESUMEN --}}
    @if ($classroom)
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="info-box shadow-sm mb-0">
                    <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Activos</span>
                        <span class="info-box-number">{{ $totalActive }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box shadow-sm mb-0">
                    <span class="info-box-icon bg-danger"><i class="fas fa-user-minus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Retirados</span>
                        <span class="info-box-number">{{ $totalRetired }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-header py-2">
                <h6 class="m-0 text-bold">
                    <i class="fas fa-users mr-1 text-primary"></i>
                    {{ $classroom->grade->grade_name }} {{ $classroom->section->section_name }} —
                    {{ $classroom->year }}
                    <span class="badge badge-secondary ml-1">{{ $totalActive + $totalRetired }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                @if ($enrollments->count() > 0)
                    <table class="table table-sm table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:45px">No.</th>
                                <th>Estudiante</th>
                                <th>Carné</th>
                                <th>Código</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width:130px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($enrollments as $idx => $enrollment)
                                <tr>
                                    <td class="text-center">{{ $enrollments->firstItem() + $idx }}</td>
                                    <td>
                                        <small>
                                            {{ $enrollment->student->user->surname }}
                                            {{ $enrollment->student->user->second_surname }},
                                            {{ $enrollment->student->user->first_name }}
                                            {{ $enrollment->student->user->middle_name }}
                                        </small>
                                    </td>
                                    <td><small class="text-muted">{{ $enrollment->student->carne ?? '—' }}</small></td>
                                    <td><small
                                            class="text-muted">{{ $enrollment->student->personal_code ?? '—' }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if ($enrollment->status === 'Activo')
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Retirado</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($enrollment->status !== 'Activo')
                                            <button wire:click="changeStatus({{ $enrollment->id }}, 'Activo')"
                                                class="btn btn-xs btn-success shadow-sm mr-1" title="Activar"><i
                                                    class="fas fa-check"></i></button>
                                        @endif
                                        @if ($enrollment->status !== 'Retirado')
                                            <button wire:click="changeStatus({{ $enrollment->id }}, 'Retirado')"
                                                class="btn btn-xs btn-danger shadow-sm" title="Retirar"><i
                                                    class="fas fa-user-minus"></i></button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-users fa-3x mb-2 text-gray"></i><br>
                        No hay estudiantes inscritos en esta aula.
                    </div>
                @endif
            </div>
            @if ($enrollments instanceof \Illuminate\Pagination\LengthAwarePaginator && $enrollments->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-right">{{ $enrollments->links() }}</div>
                </div>
            @endif
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i> Seleccione año, nivel, grado y sección para ver los estudiantes
            inscritos.
        </div>
    @endif

    {{-- ==========================================
         MODAL
    =========================================== --}}
    <div wire:ignore.self class="modal fade" id="EnrollmentModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog {{ $modalMode === 'new' ? 'modal-xl' : 'modal-md' }}">
            <div class="modal-content">
                <div class="modal-header {{ $modalMode === 'new' ? 'bg-primary' : 'bg-success' }}">
                    <h5 class="modal-title text-white">
                        <i class="fas {{ $modalMode === 'new' ? 'fa-user-plus' : 'fa-user-check' }} mr-1"></i>
                        {{ $modalMode === 'new' ? 'Inscribir Nuevo Estudiante' : 'Inscribir Estudiante Existente' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"
                        wire:click="resetModal"><span>×</span></button>
                </div>

                <div class="modal-body p-0">

                    @if ($modalMode === 'existing')
                        <div class="p-3">
                            <div class="form-group position-relative">
                                <label class="text-sm">Buscar por nombre, CUI, carné o código personal</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="fas fa-search"></i></span></div>
                                    <input type="text" wire:model.live.debounce.300ms="searchStudent"
                                        class="form-control" placeholder="Escriba al menos 2 caracteres..." autocomplete="new-password">
                                </div>
                                @if (count($searchResults) > 0)
                                    <div class="list-group shadow position-absolute w-100"
                                        style="z-index:999; top:100%">
                                        @foreach ($searchResults as $result)
                                            <button type="button"
                                                wire:click="selectStudent({{ $result['id'] }}, '{{ addslashes($result['name']) }}')"
                                                class="list-group-item list-group-item-action py-2 text-sm">
                                                <strong>{{ $result['name'] }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    CUI: {{ $result['cui'] }} &nbsp;|&nbsp;
                                                    Carné: {{ $result['carne'] }} &nbsp;|&nbsp;
                                                    Código: {{ $result['personal_code'] }}
                                                </small>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @if ($selectedStudentId)
                                <div class="alert alert-success py-2 text-sm mt-2">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Seleccionado: <strong>{{ $selectedStudentName }}</strong>
                                </div>
                            @endif
                            @error('selectedStudentId')
                                <div class="alert alert-danger py-2 text-sm">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        {{-- TABS nuevo estudiante --}}
                        <ul class="nav nav-tabs custom-tabs px-3 pt-2" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'general' ? 'active font-weight-bold text-primary' : '' }}"
                                    wire:click.prevent="$set('activeTab','general')" href="#">
                                    <i class="fas fa-id-card mr-1"></i> Datos Generales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'medical' ? 'active font-weight-bold text-primary' : '' }}"
                                    wire:click.prevent="$set('activeTab','medical')" href="#">
                                    <i class="fas fa-notes-medical mr-1"></i> Ficha Médica
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'guardians' ? 'active font-weight-bold text-primary' : '' }}"
                                    wire:click.prevent="$set('activeTab','guardians')" href="#">
                                    <i class="fas fa-users mr-1"></i> Padres/Encargado
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-3">

                            {{-- ==========================================
                                 TAB: DATOS GENERALES
                            =========================================== --}}
                            <div class="{{ $activeTab === 'general' ? 'd-block' : 'd-none' }}">
                                <div class="row">
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">CUI <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-id-card"></i></span></div>
                                            <input type="text" wire:model="cui"
                                                class="form-control @error('cui') is-invalid @enderror">
                                        </div>
                                        @error('cui')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Carné</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-id-badge"></i></span></div>
                                            <input type="text" wire:model="carne" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Código Personal</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-hashtag"></i></span></div>
                                            <input type="text" wire:model="personal_code" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Género <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-venus-mars"></i></span></div>
                                            <select wire:model="gender"
                                                class="form-control @error('gender') is-invalid @enderror">
                                                <option value="">Seleccione...</option>
                                                <option value="Masculino">Masculino</option>
                                                <option value="Femenino">Femenino</option>
                                            </select>
                                        </div>
                                        @error('gender')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Primer Nombre <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-user"></i></span></div>
                                            <input type="text" wire:model="first_name"
                                                class="form-control @error('first_name') is-invalid @enderror">
                                        </div>
                                        @error('first_name')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Segundo Nombre</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-user"></i></span></div>
                                            <input type="text" wire:model="middle_name" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Primer Apellido <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-user"></i></span></div>
                                            <input type="text" wire:model="surname"
                                                class="form-control @error('surname') is-invalid @enderror">
                                        </div>
                                        @error('surname')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Segundo Apellido</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-user"></i></span></div>
                                            <input type="text" wire:model="second_surname" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Apellido Casada</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-user"></i></span></div>
                                            <input type="text" wire:model="married_surname" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Fecha de Nacimiento <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-calendar"></i></span></div>
                                            <input type="date" wire:model="birthdate"
                                                class="form-control @error('birthdate') is-invalid @enderror">
                                        </div>
                                        @error('birthdate')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Estado Civil</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-ring"></i></span></div>
                                            <select wire:model="civil_status" class="form-control">
                                                <option value="">Seleccione...</option>
                                                <option value="Soltero">Soltero(a)</option>
                                                <option value="Casado">Casado(a)</option>
                                                <option value="Divorciado">Divorciado(a)</option>
                                                <option value="Viudo">Viudo(a)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Celular</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-phone"></i></span></div>
                                            <input type="text" wire:model="cellphone" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="text-sm mb-1">
                                            Correo Institucional
                                            @if ($requireInstitutionalEmail)
                                                <span class="text-danger">*</span>
                                            @else
                                                <span class="badge badge-secondary ml-1"
                                                    style="font-size:0.7rem">Auto</span>
                                            @endif
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            </div>
                                            <input type="email" wire:model="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                @if (!$requireInstitutionalEmail) readonly @endif>
                                        </div>
                                        @error('email')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                        @if (!$requireInstitutionalEmail)
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle mr-1"></i>Se usará el correo personal.
                                            </small>
                                        @endif
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="text-sm mb-1">
                                            Correo Personal
                                            @if (!$requireInstitutionalEmail)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-envelope"></i></span></div>
                                            <input type="email" wire:model="personal_email"
                                                class="form-control @error('personal_email') is-invalid @enderror">
                                        </div>
                                        @error('personal_email')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    @if ($requireInstitutionalEmail)
                                        <div class="col-md-4 form-group mb-2">
                                            <label class="text-sm mb-1">Contraseña <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                </div>
                                                <input type="password" wire:model="password"
                                                    class="form-control @error('password') is-invalid @enderror">
                                            </div>
                                            @error('password')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    @else
                                        <div class="col-md-4 form-group mb-2">
                                            <label class="text-sm mb-1">Contraseña</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                </div>
                                                <input type="password" class="form-control" value="password"
                                                    readonly>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle mr-1"></i>Contraseña por defecto:
                                                <code>password</code>
                                            </small>
                                        </div>
                                    @endif
                                    <div class="col-12 form-group mb-2">
                                        <label class="text-sm mb-1">Dirección</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-map-marker-alt"></i></span></div>
                                            <input type="text" wire:model="address" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12 mt-1">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" wire:model.live="is_own_guardian"
                                                class="custom-control-input" id="is_own_guardian_modal">
                                            <label class="custom-control-label text-sm" for="is_own_guardian_modal">
                                                El estudiante es su propio encargado
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ==========================================
                                 TAB: FICHA MÉDICA
                            =========================================== --}}
                            <div class="{{ $activeTab === 'medical' ? 'd-block' : 'd-none' }}">
                                <div class="row">
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="text-sm mb-1">Tipo de Sangre</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-tint text-danger"></i></span></div>
                                            <select wire:model="blood_type" class="form-control">
                                                <option value="">Seleccione...</option>
                                                <option value="O+">O Positivo (O+)</option>
                                                <option value="O-">O Negativo (O-)</option>
                                                <option value="A+">A Positivo (A+)</option>
                                                <option value="A-">A Negativo (A-)</option>
                                                <option value="B+">B Positivo (B+)</option>
                                                <option value="B-">B Negativo (B-)</option>
                                                <option value="AB+">AB Positivo (AB+)</option>
                                                <option value="AB-">AB Negativo (AB-)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="text-sm mb-1">Peso (lb)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-weight"></i></span></div>
                                            <input type="number" step="0.01" wire:model="weight"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="text-sm mb-1">Estatura (m)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-ruler-vertical"></i></span></div>
                                            <input type="number" step="0.01" wire:model="height"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <hr class="my-2">
                                    </div>
                                    @foreach ([['takes_medication', 'medication_description', '¿Toma medicamentos?', 'Especifique cuáles...'], ['has_disease', 'disease_description', '¿Padece alguna enfermedad?', 'Especifique cuál...'], ['has_allergies', 'allergies_description', '¿Tiene alergias?', 'Especifique cuáles...'], ['had_surgery', 'surgery_description', '¿Tuvo cirugía previa?', 'Especifique cuáles...']] as [$toggle, $desc, $label, $placeholder])
                                        <div class="col-md-4 form-group mb-2">
                                            <div class="custom-control custom-switch mt-1">
                                                <input type="checkbox" wire:model.live="{{ $toggle }}"
                                                    class="custom-control-input" id="{{ $toggle }}_enroll">
                                                <label class="custom-control-label text-sm"
                                                    for="{{ $toggle }}_enroll">{{ $label }}</label>
                                            </div>
                                        </div>
                                        <div class="col-md-8 form-group mb-2">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend"><span class="input-group-text"><i
                                                            class="fas fa-pen"></i></span></div>
                                                <input type="text" wire:model="{{ $desc }}"
                                                    class="form-control" placeholder="{{ $placeholder }}"
                                                    @if (!$this->$toggle) disabled @endif>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- ==========================================
                                 TAB: GUARDIANES
                            =========================================== --}}
                            <div class="{{ $activeTab === 'guardians' ? 'd-block' : 'd-none' }}">
                                @foreach ([['padre', 'Padre', 'fa-male', 'bg-primary'], ['madre', 'Madre', 'fa-female', 'bg-pink'], ['encargado', 'Encargado', 'fa-user-shield', 'bg-secondary']] as [$key, $label, $icon, $color])
                                    <div class="card card-outline card-secondary mb-3">
                                        <div class="card-header py-2">
                                            <div class="d-flex align-items-center">
                                                <div class="custom-control custom-switch mr-3">
                                                    <input type="checkbox"
                                                        wire:model.live="guardians.{{ $key }}.enabled"
                                                        class="custom-control-input"
                                                        id="guardian_{{ $key }}_enabled">
                                                    <label class="custom-control-label text-sm font-weight-bold"
                                                        for="guardian_{{ $key }}_enabled">
                                                        <i class="fas {{ $icon }} mr-1"></i>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                                @if (!$guardians[$key]['enabled'])
                                                    <small class="text-muted">No registrado</small>
                                                @else
                                                    <small class="text-success"><i
                                                            class="fas fa-check-circle mr-1"></i> Activo</small>
                                                @endif
                                            </div>
                                        </div>
                                        @if ($guardians[$key]['enabled'])
                                            <div class="card-body">

                                                {{-- INICIO DEL BLOQUE NUEVO DE RADIO BUTTONS --}}
                                                @if ($key === 'encargado')
                                                    <div class="row mb-3 pb-3 border-bottom">
                                                        <div class="col-12">
                                                            <label class="text-sm d-block mb-2 text-primary">
                                                                <i class="fas fa-magic mr-1"></i> ¿Desea autocompletar
                                                                la información del encargado?
                                                            </label>
                                                            <div
                                                                class="custom-control custom-radio custom-control-inline">
                                                                <input type="radio" id="encargado_estudiante"
                                                                    wire:model.live="encargado_role"
                                                                    value="estudiante" class="custom-control-input">
                                                                <label class="custom-control-label text-sm"
                                                                    for="encargado_estudiante">El mismo
                                                                    estudiante</label>
                                                            </div>
                                                            <div
                                                                class="custom-control custom-radio custom-control-inline">
                                                                <input type="radio" id="encargado_padre"
                                                                    wire:model.live="encargado_role" value="padre"
                                                                    class="custom-control-input">
                                                                <label class="custom-control-label text-sm"
                                                                    for="encargado_padre">Papá</label>
                                                            </div>
                                                            <div
                                                                class="custom-control custom-radio custom-control-inline">
                                                                <input type="radio" id="encargado_madre"
                                                                    wire:model.live="encargado_role" value="madre"
                                                                    class="custom-control-input">
                                                                <label class="custom-control-label text-sm"
                                                                    for="encargado_madre">Mamá</label>
                                                            </div>
                                                            <div
                                                                class="custom-control custom-radio custom-control-inline">
                                                                <input type="radio" id="encargado_otro"
                                                                    wire:model.live="encargado_role" value="otro"
                                                                    class="custom-control-input">
                                                                <label class="custom-control-label text-sm"
                                                                    for="encargado_otro">Otra persona (Manual)</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                {{-- FIN DEL BLOQUE NUEVO --}}

                                                <div class="row">
                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Nombres <span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-user"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.first_name"
                                                                class="form-control @error('guardians.' . $key . '.data.first_name') is-invalid @enderror">
                                                        </div>
                                                        @error('guardians.' . $key . '.data.first_name')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Apellido <span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-user"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.last_name"
                                                                class="form-control @error('guardians.' . $key . '.data.last_name') is-invalid @enderror">
                                                        </div>
                                                        @error('guardians.' . $key . '.data.last_name')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">CUI <span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-id-card"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.cui"
                                                                class="form-control @error('guardians.' . $key . '.data.cui') is-invalid @enderror">
                                                        </div>
                                                        @error('guardians.' . $key . '.data.cui')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Fecha de Nacimiento <span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-calendar"></i></span></div>
                                                            <input type="date"
                                                                wire:model="guardians.{{ $key }}.data.birthdate"
                                                                class="form-control @error('guardians.' . $key . '.data.birthdate') is-invalid @enderror">
                                                        </div>
                                                        @error('guardians.' . $key . '.data.birthdate')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Lugar de Nacimiento</label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-map-pin"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.birthplace"
                                                                class="form-control">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Extendido en <span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-map-marker-alt"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.cui_extended_in"
                                                                class="form-control @error('guardians.' . $key . '.data.cui_extended_in') is-invalid @enderror">
                                                        </div>
                                                        @error('guardians.' . $key . '.data.cui_extended_in')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Nacionalidad <span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-flag"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.nationality"
                                                                class="form-control @error('guardians.' . $key . '.data.nationality') is-invalid @enderror">
                                                        </div>
                                                        @error('guardians.' . $key . '.data.nationality')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Profesión <span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-briefcase"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.profession"
                                                                class="form-control @error('guardians.' . $key . '.data.profession') is-invalid @enderror">
                                                        </div>
                                                        @error('guardians.' . $key . '.data.profession')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Teléfono <span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-phone"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.phone"
                                                                class="form-control @error('guardians.' . $key . '.data.phone') is-invalid @enderror">
                                                        </div>
                                                        @error('guardians.' . $key . '.data.phone')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Correo</label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-envelope"></i></span></div>
                                                            <input type="email"
                                                                wire:model="guardians.{{ $key }}.data.email"
                                                                class="form-control">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-8 form-group mb-2">
                                                        <label class="text-sm mb-1">Dirección de Residencia <span
                                                                class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-home"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.residence_address"
                                                                class="form-control @error('guardians.' . $key . '.data.residence_address') is-invalid @enderror">
                                                        </div>
                                                        @error('guardians.' . $key . '.data.residence_address')
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="col-12">
                                                        <hr class="my-2"><small
                                                            class="text-muted text-uppercase font-weight-bold">Datos
                                                            Laborales</small>
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Empresa</label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-building"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.company_name"
                                                                class="form-control">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Tel. Empresa</label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-phone-square-alt"></i></span>
                                                            </div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.company_phone"
                                                                class="form-control">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4 form-group mb-2">
                                                        <label class="text-sm mb-1">Dirección Empresa</label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend"><span
                                                                    class="input-group-text"><i
                                                                        class="fas fa-map-marker-alt"></i></span></div>
                                                            <input type="text"
                                                                wire:model="guardians.{{ $key }}.data.company_address"
                                                                class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                        </div>{{-- fin tab-content --}}
                    @endif
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetModal">
                        Cancelar
                    </button>
                    @if ($modalMode === 'existing')
                        <button wire:click.prevent="enrollExisting" type="button" class="btn btn-success btn-sm"
                            wire:loading.attr="disabled" wire:target="enrollExisting">
                            <span wire:loading.remove wire:target="enrollExisting"><i class="fas fa-user-check"></i>
                                Inscribir</span>
                            <span wire:loading wire:target="enrollExisting"><i class="fas fa-spinner fa-pulse"></i>
                                Inscribiendo...</span>
                        </button>
                    @else
                        <button wire:click.prevent="enrollNew" type="button" class="btn btn-primary btn-sm"
                            wire:loading.attr="disabled" wire:target="enrollNew">
                            <span wire:loading.remove wire:target="enrollNew"><i class="fas fa-user-plus"></i> Crear e
                                Inscribir</span>
                            <span wire:loading wire:target="enrollNew"><i class="fas fa-spinner fa-pulse"></i>
                                Procesando...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <style>
            .custom-tabs .nav-link {
                border-top-width: 3px;
                border-top-color: transparent;
            }

            .custom-tabs .nav-link.active {
                border-top-color: #007bff;
                font-weight: bold;
                background-color: #f8f9fa;
            }

            .bg-pink {
                background-color: #e83e8c !important;
            }
        </style>
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('openEnrollmentModal', () => $('#EnrollmentModal').modal('show'));
                Livewire.on('closeModalMessaje', (event) => {
                    let payload = event[0] || event;
                    $('#' + payload.modalId).modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    Swal.fire({
                        position: 'top-end',
                        icon: payload.type,
                        title: payload.title,
                        text: payload.message,
                        showConfirmButton: false,
                        timer: 3000
                    });
                });
            });
        </script>
    @endpush
</div>
