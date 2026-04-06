@extends('adminlte::page')

@section('title', 'Profesores y Cursos Asignados')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-chalkboard-teacher mr-2"></i> Profesores y Cursos Asignados
    </h1>
@endsection

@section('content')
    <livewire:admin.reports.professor-courses-excel />
@endsection
