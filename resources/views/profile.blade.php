@extends('adminlte::page')

@section('title', 'Perfil de Usuario')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-user-circle"></i> Perfil de usuario</h1>
@stop

@section('content')
    {{-- Componente Principal (Siempre visible) --}}
    <livewire:profile.update-profile />

    {{-- CONDICIÓN 1: Solo si es Profesor --}}
    @if (auth()->user()->hasRole('Profesor'))
        <livewire:profile.update-professor-info />
    @endif

    {{-- CONDICIÓN 2: Si NO es Estudiante (Cualquier otro rol puede ver/editar) --}}
    @if (!auth()->user()->hasRole('Estudiante'))
        <livewire:profile.update-medical-info />
    @endif
@stop

@section('css')

@stop

@section('js')

@stop
