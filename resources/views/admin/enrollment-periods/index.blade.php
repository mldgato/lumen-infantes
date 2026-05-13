@extends('adminlte::page')

@section('title', 'Períodos de Inscripción')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark text-sm font-weight-bold">
            <i class="fas fa-calendar-alt mr-2 text-primary"></i>Períodos de Inscripción
        </h1>
    </div>
@endsection

@section('content')
    <livewire:admin.enrollment-periods />
@endsection
