@extends('adminlte::page')

@section('title', 'Aulas')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-school"></i> Aulas</h1>
@stop

@section('content')
    <livewire:admin.classrooms />
@stop

@section('css')
@stop

@section('js')
@stop
