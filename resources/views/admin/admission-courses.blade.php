@extends('adminlte::page')

@section('title', 'Materias de Admisión')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-book mr-2"></i> Materias de Admisión
    </h1>
@endsection

@section('content')
    <livewire:admin.admission-courses />
@endsection
