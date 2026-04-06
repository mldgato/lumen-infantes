@extends('adminlte::page')

@section('title', 'Registro de Asistencia')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-user-check"></i> Registro de Asistencia</h1>
@stop

@section('content')
    <livewire:profesor.take-attendance />
@stop
