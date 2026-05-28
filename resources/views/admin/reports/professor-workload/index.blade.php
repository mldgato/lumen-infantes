@extends('adminlte::page')

@section('title', 'Carga Docente')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-briefcase mr-2"></i> Carga Docente por Profesor
    </h1>
@endsection

@section('content')
    <livewire:admin.reports.professor-workload />
@endsection
