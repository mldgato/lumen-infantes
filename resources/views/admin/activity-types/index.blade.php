@extends('adminlte::page')

@section('title', 'Tipos de Actividad')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-tag"></i> Tipos de Actividad</h1>
@stop

@section('content')
    <livewire:admin.activity-types />
@stop

@section('css')
@stop

@section('js')
@stop
