@extends('adminlte::page')

@section('title', 'Descarga de Cuadros por Aula')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-file-pdf mr-2"></i> Descarga de Cuadros por Aula
    </h1>
@endsection

@section('content')
    <livewire:admin.reports.cuadros-classroom />
@endsection
