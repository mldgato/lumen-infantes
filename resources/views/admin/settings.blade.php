@extends('adminlte::page')

@section('title', 'Configuraciones del Sistema')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-cogs mr-2"></i> Configuraciones del Sistema
    </h1>
@endsection

@section('content')
    <livewire:admin.system-settings />
@endsection
