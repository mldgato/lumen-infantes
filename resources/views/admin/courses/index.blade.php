@extends('adminlte::page')

@section('title', 'Cursos')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-book"></i> Cursos</h1>
@stop

@section('content')
    <livewire:admin.courses />
@stop

@section('css')
@stop

@section('js')
@stop
