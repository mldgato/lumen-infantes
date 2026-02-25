@extends('adminlte::page')

@section('title', 'Directorio de Estudiantes')

@section('content_header')
    <h1>Gestión de Estudiantes</h1>
@stop

@section('content')
    <livewire:students.student-list />
@stop
