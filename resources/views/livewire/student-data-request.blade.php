<div class="public-card">

    <div class="public-header">
        <h1><i class="fas fa-edit mr-2"></i>Actualizar Datos</h1>
        <p>{{ config('app.institution_name', 'EduCheck') }}</p>
    </div>

    <div class="public-body">

        {{-- ✅ Ya actualizado este año --}}
        @if ($alreadyUpdated)
            <div class="alert alert-warning text-center">
                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                <strong>Ya actualizaste tu información.</strong><br>
                Registro del <strong>{{ $updatedAtLabel }}</strong>.<br>
                <small class="text-muted">Solo se permite una actualización por año.</small>
            </div>
            <div class="text-center mt-3">
                <button wire:click="$set('alreadyUpdated', false)" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </button>
            </div>

        {{-- ✅ Correo enviado --}}
        @elseif ($submitted)
            <div class="alert alert-success text-center">
                <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                <strong>¡Revisa tu correo!</strong><br>
                Enviamos un enlace a <strong>{{ $email }}</strong>.<br>
                <small class="text-muted">El enlace expira en 60 minutos.</small>
            </div>

        {{-- 📋 Formulario de identificación --}}
        @else
            <p class="text-muted text-sm mb-4">
                Ingresa tu código de identificación y un correo electrónico.
                Te enviaremos un enlace para completar la actualización.
            </p>

            <form wire:submit="submit">

                <div class="form-group">
                    <label class="font-weight-bold text-sm">
                        <i class="fas fa-id-card mr-1 text-muted"></i>
                        Código personal o carné
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           wire:model="code"
                           class="form-control @error('code') is-invalid @enderror"
                           placeholder="Ej: 2024001 o A-123"
                           autofocus>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="font-weight-bold text-sm">
                        <i class="fas fa-envelope mr-1 text-muted"></i>
                        Correo electrónico
                        <span class="text-danger">*</span>
                    </label>
                    <input type="email"
                           wire:model="email"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="tucorreo@ejemplo.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Recibirás el enlace de actualización en esta dirección.
                    </small>
                </div>

                <button type="submit" class="btn btn-success btn-block" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="fas fa-paper-plane mr-1"></i> Enviar enlace
                    </span>
                    <span wire:loading>
                        <i class="fas fa-spinner fa-spin mr-1"></i> Enviando...
                    </span>
                </button>

            </form>
        @endif

    </div>

    <div class="public-footer">
        &copy; {{ date('Y') }} {{ config('app.institution_name', 'EduCheck') }}
    </div>

</div>
