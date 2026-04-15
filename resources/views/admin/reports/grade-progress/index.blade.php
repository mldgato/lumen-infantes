@extends('adminlte::page')

@section('title', 'Avance de Ingreso de Calificaciones')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-chart-line mr-2"></i> Avance de Ingreso de Calificaciones
    </h1>
@endsection

@section('content')
    <livewire:admin.reports.grade-progress />
@endsection
