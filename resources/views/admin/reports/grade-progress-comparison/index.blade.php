@extends('adminlte::page')

@section('title', 'Comparativo por Unidad')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-chart-bar mr-2"></i> Comparativo de Rendimiento por Unidad
    </h1>
@endsection

@section('content')
    <livewire:admin.reports.grade-progress-comparison />
@endsection
