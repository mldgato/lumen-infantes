@extends('adminlte::page')

@section('title', 'Vista Cuadrícula')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-th"></i> Vista Cuadrícula de Calificaciones</h1>
@stop

@section('content')
    <livewire:profesor.grade-book-grid :gradeBook="$gradeBook" />
@stop

@section('css')
@stop

@section('js')
@stop
