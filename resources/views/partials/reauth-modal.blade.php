<div class="modal fade" id="reauthModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-lock mr-2"></i>Sesión expirada
                </h5>
            </div>

            <div class="modal-body">
                <p class="text-muted text-sm mb-3">
                    Tu sesión expiró por inactividad. Ingresa tu contraseña para continuar.
                </p>

                <div class="form-group mb-2">
                    <label class="text-sm">Usuario</label>
                    <input type="text" class="form-control form-control-sm" id="reauthEmail" readonly>
                </div>

                <div class="form-group mb-0">
                    <label class="text-sm">Contraseña</label>
                    <input type="password" class="form-control form-control-sm" id="reauthPassword"
                        placeholder="Tu contraseña">
                    <span class="text-danger text-sm d-none" id="reauthError"></span>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <a href="{{ route('login') }}" class="btn btn-sm btn-default">
                    <i class="fas fa-sign-out-alt mr-1"></i>Ir al login
                </a>
                <button type="button" class="btn btn-sm btn-warning" id="reauthSubmit">
                    <span id="reauthBtnText">
                        <i class="fas fa-key mr-1"></i>Continuar
                    </span>
                    <span id="reauthBtnLoading" class="d-none">
                        <i class="fas fa-spinner fa-spin mr-1"></i>Verificando...
                    </span>
                </button>
            </div>

        </div>
    </div>
</div>
