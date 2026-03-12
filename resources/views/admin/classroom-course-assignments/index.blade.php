@extends('adminlte::page')

@section('title', 'Asignación de Profesores')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-chalkboard-teacher"></i> Asignación de Profesores</h1>
@stop

@section('content')
    <livewire:admin.classroom-course-assignments />
@stop

@section('css')
@stop

@section('js')
@stop
