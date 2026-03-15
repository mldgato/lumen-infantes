@extends('adminlte::page')

@section('title', 'Listado de Estudiantes')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-list mr-2"></i> Listado de Estudiantes
    </h1>
@endsection

@section('content')
    <livewire:admin.reports.student-list />
@endsection
