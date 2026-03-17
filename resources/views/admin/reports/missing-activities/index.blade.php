@extends('adminlte::page')

@section('title', 'Actividades No Entregadas')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white"><i class="fas fa-tasks mr-2"></i> Actividades No Entregadas por Aula</h1>
@endsection

@section('content')
    <livewire:admin.reports.missing-activities />
@endsection
