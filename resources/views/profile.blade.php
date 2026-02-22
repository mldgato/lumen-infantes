@extends('adminlte::page')

@section('title', 'Perfil de Usuario')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="text-white"><i class="fas fa-user-circle"></i> Perfil de usuario</h1>
@stop

@section('content')
    <livewire:profile.update-profile />
@stop

@section('css')

@stop

@section('js')

@stop
