@extends('adminlte::page')

@section('title', 'Facturación de Admisiones')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-file-invoice-dollar mr-2"></i> Facturación de Admisiones
    </h1>
@endsection

@section('content')
    <livewire:admin.students.admission-billing-list />
@endsection
