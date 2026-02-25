@extends('adminlte::page')

@section('title', 'Personal y Docentes')

@section('content_header')
    <h1>Gestión de Personal</h1>
@stop

@section('content')
    <livewire:users.user-list />
@stop
