@extends('adminlte::page')

@section('title', 'Niveles')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-layer-group"></i> Niveles</h1>
@stop

@section('content')
    <livewire:admin.levels />
@stop

@section('css')
@stop

@section('js')
@stop
