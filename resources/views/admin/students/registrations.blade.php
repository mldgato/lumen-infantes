@extends('adminlte::page')

@section('title', 'Inscripciones')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-user-graduate mr-2"></i> Inscripciones de Estudiantes
    </h1>
@endsection

@section('content')
    <livewire:admin.students.enrollment-list />
@endsection
