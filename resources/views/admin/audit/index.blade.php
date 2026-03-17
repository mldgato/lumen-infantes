@extends('adminlte::page')

@section('title', 'Auditoría')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-history mr-2"></i> Registro de Auditoría
    </h1>
@endsection

@section('content')
    <livewire:admin.audit-log />
@endsection
