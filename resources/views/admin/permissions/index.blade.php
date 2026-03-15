@extends('adminlte::page')

@section('title', 'Permisos')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-key mr-2"></i> Permisos
    </h1>
@endsection

@section('content')
    <livewire:admin.permissions.show-permissions />
@endsection
