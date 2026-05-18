@extends('adminlte::page')

@section('title', 'Resumen de Actividades')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white"><i class="fas fa-table mr-2"></i> Resumen de Actividades por Estudiante</h1>
@endsection

@section('content')
    <livewire:admin.reports.activity-summary />
@endsection
