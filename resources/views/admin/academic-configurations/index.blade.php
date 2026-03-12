@extends('adminlte::page')

@section('title', 'Configuración Académica')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-cogs"></i> Configuración Académica</h1>
@stop

@section('content')
    <livewire:admin.academic-configurations />
@stop

@section('css')
@stop

@section('js')
@stop
