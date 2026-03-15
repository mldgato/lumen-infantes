@extends('adminlte::page')

@section('title', 'Sábana de Calificaciones')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-file-pdf mr-2"></i> Sábana de Calificaciones
    </h1>
@endsection

@section('content')
    <livewire:profesor.reports.sabana-promedio />
@endsection
