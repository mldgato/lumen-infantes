@extends('adminlte::page')

@section('title', 'Estudiantes en Riesgo')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-exclamation-triangle mr-2"></i> Estudiantes en Riesgo de Reprobación
    </h1>
@endsection

@section('content')
    <livewire:admin.reports.students-at-risk />
@endsection
