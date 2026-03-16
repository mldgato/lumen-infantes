@extends('adminlte::page')

@section('title', 'Cambio de Calificaciones')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-edit mr-2"></i> Solicitud de Cambio de Calificaciones
    </h1>
@endsection

@section('content')
    <livewire:profesor.grade-change-requests />
@endsection
