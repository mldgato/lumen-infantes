@extends('adminlte::page')

@section('title', 'Historial de Estudiante')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-history mr-2"></i> Historial de Calificaciones por Estudiante
    </h1>
@endsection

@section('content')
    <livewire:admin.reports.student-history />
@endsection
