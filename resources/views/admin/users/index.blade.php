@extends('adminlte::page')

@section('title', 'Personal y Docentes')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-chalkboard-teacher"></i> Gestión de Personal</h1>
@stop

@section('content')
    <livewire:users.user-list />
@stop
