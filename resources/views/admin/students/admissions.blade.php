@extends('adminlte::page')

@section('title', 'Solicitudes de Admisión')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-file-signature mr-2"></i> Solicitudes de Admisión
    </h1>
@endsection

@section('content')
    <livewire:admin.students.admission-list />
@endsection
