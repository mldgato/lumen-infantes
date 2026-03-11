@extends('adminlte::page')

@section('title', 'Pénsum')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-list-alt"></i> Pénsum</h1>
@stop

@section('content')
    <livewire:admin.pensums />
@stop

@section('css')
@stop

@section('js')
@stop
