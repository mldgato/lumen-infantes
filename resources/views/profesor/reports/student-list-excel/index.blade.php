@extends('adminlte::page')

@section('title', 'Listado de Estudiantes Excel')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-file-excel mr-2"></i> Listado de Estudiantes Excel
    </h1>
@endsection

@section('content')
    <livewire:profesor.reports.student-list-excel />
@endsection
