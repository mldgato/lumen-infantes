@extends('adminlte::page')

@section('title', 'Evaluaciones Académicas')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-graduation-cap mr-2"></i> Evaluaciones Académicas
    </h1>
@endsection

@section('content')
    <livewire:admin.students.admission-academic-list />
@endsection
