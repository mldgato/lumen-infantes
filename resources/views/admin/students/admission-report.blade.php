@extends('adminlte::page')

@section('title', 'Reporte de Admisiones')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-file-excel mr-2"></i> Reporte de Solicitudes de Admisión
    </h1>
@endsection

@section('content')
    <livewire:admin.students.admission-report />
@endsection
