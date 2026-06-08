@extends('adminlte::page')

@section('title', 'Actualización de Notas')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-edit mr-2"></i> Actualización de Notas
    </h1>
@endsection

@section('content')
    <livewire:admin.students.student-selector />
@endsection
