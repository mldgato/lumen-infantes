<div>
    @section('auth_header', 'Actualización Requerida')

    <p class="login-box-msg text-danger font-weight-bold pb-1">
        <i class="fas fa-lock mr-1"></i> Seguridad de la cuenta
    </p>
    <p class="text-muted text-center text-sm mb-4">
        Debes actualizar tu contraseña para poder acceder a <b>EduCheck</b>.
    </p>

    <form wire:submit="updatePassword">
        {{-- Campo Nueva Contraseña --}}
        <div class="input-group mb-3">
            <input type="password" id="password" wire:model="password"
                class="form-control @error('password') is-invalid @enderror"
                placeholder="Nueva Contraseña (mín. 8 caracteres)">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-key"></span></div>
            </div>
            @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        {{-- Confirmar Contraseña --}}
        <div class="input-group mb-4">
            <input type="password" id="password_confirmation" wire:model="password_confirmation" class="form-control"
                placeholder="Confirmar Nueva Contraseña">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block mb-3">
            <i class="fas fa-save mr-2"></i> {{ __('Change Password') }}
        </button>
    </form>

    @section('auth_footer')
        <div class="d-flex justify-content-end text-sm">
            {{-- Formulario de Cerrar Sesión --}}
            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="text-danger">
                    <i class="fas fa-sign-out-alt mr-1"></i> Salir
                </a>
            </form>
        </div>
    @stop
</div>
