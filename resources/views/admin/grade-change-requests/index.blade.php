@extends('adminlte::page')

@section('title', 'Solicitudes de Cambio de Notas')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-clipboard-check mr-2"></i> Solicitudes de Cambio de Calificaciones
    </h1>
@endsection

@section('content')
    <livewire:admin.grade-change-requests />
@endsection
