@extends('adminlte::page')

@section('title', 'Profesores')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-chalkboard-teacher"></i> Gestión de Profesores</h1>
@stop

@section('content')
    <livewire:admin.professors />
@stop

@section('css')
@stop

@section('js')
@stop
