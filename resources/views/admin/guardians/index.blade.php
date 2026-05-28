@extends('adminlte::page')

@section('title', 'Guardianes')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-user-friends mr-2"></i> Guardianes / Tutores
    </h1>
@endsection

@section('content')
    <livewire:admin.guardians />
@endsection
