@extends('adminlte::page')

@section('title', 'Secciones')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-tags"></i> Secciones</h1>
@stop

@section('content')
    <livewire:admin.sections />
@stop

@section('css')
@stop

@section('js')
@stop
