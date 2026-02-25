<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title mt-1">Directorio de Estudiantes</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control float-right"
                    placeholder="Buscar por nombre, CUI o correo...">
                <div class="input-group-append">
                    <button type="button" class="btn btn-default">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped text-nowrap">
            <thead>
                <tr>
                    <th>CUI</th>
                    <th>Nombre Completo</th>
                    <th>Correo</th>
                    <th>Celular</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td>{{ $student->cui }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->cellphone ?? 'N/A' }}</td>
                        <td>
                            @if ($student->is_active)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info" title="Ver Perfil"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-user-graduate fa-3x mb-3 d-block text-gray"></i>
                            No se encontraron estudiantes con ese criterio de búsqueda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        <div class="float-right">
            {{ $students->links() }}
        </div>
    </div>
</div>
