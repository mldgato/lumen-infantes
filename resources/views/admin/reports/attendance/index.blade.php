@extends('adminlte::page')

@section('title', 'Reporte de Asistencia')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-user-check mr-2"></i> Reporte de Asistencia
    </h1>
@endsection

@section('content')
    <livewire:admin.reports.attendance-report />
@endsection
