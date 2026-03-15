@extends('adminlte::page')

@section('title', 'Cuadro Vacío')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop


@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-file-pdf mr-2"></i> Imprimir Cuadro Vacío
    </h1>
@endsection

@section('content')
    <livewire:profesor.reports.cuadro-vacio />
@endsection
