@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header', __('Forgot password'))

@section('auth_body')
    {{-- Mensaje de éxito cuando se envía el correo --}}
    @if (session('status'))
        <div class="alert alert-success text-center">
            {{ session('status') }}
        </div>
    @endif

    <p class="login-box-msg">{{ __('Enter your email to receive a password reset link') }}</p>

    <form action="{{ route('password.email') }}" method="post">
        @csrf

        {{-- Campo Correo Electrónico --}}
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}" placeholder="{{ __('Email Address') }}" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">
                    {{ __('Email password reset link') }}
                </button>
            </div>
        </div>
    </form>
@stop

@section('auth_footer')
    <p class="mt-3 mb-1 text-center">
        <a href="{{ route('login') }}" wire:navigate>{{ __('Or, return to log in') }}</a>
    </p>
@stop
