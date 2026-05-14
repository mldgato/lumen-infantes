@extends('adminlte::page')

@section('title', 'Mi Asistencia')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-user-check mr-2"></i> Mi Asistencia</h1>
@stop

@section('content')
    <livewire:student.my-attendance />
@stop
