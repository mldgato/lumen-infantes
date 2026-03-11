@extends('adminlte::page')

@section('title', 'Grados')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-graduation-cap"></i> Grados</h1>
@stop

@section('content')
    <livewire:admin.grades />
@stop

@section('css')
@stop

@section('js')
@stop
