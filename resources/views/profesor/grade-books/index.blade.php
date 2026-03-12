@extends('adminlte::page')

@section('title', 'Mis Cuadros de Calificaciones')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-book-open"></i> Mis Cuadros de Calificaciones</h1>
@stop

@section('content')
    <livewire:profesor.grade-books />
@stop

@section('css')
@stop

@section('js')
@stop
