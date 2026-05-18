@extends('adminlte::page')

@section('title', 'Actividades por Estudiante')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white"><i class="fas fa-user-graduate mr-2"></i> Actividades por Estudiante</h1>
@endsection

@section('content')
    <livewire:admin.reports.student-activity-detail />
@endsection

@push('js')
@endpush
