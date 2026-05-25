@extends('adminlte::page')

@section('title', 'Guardianes')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-user-friends"></i> Gestión de Guardianes</h1>
@stop

@section('content')
    <livewire:admin.guardians />
@stop

@section('css')
@stop

@section('js')
@stop
