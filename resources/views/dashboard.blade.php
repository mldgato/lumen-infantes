@extends('adminlte::page')

@section('title', 'Dashboard')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-tachometer-alt mr-2"></i>
        @can('admin.grade-books.index')
            Panel de Administración
        @else
            Mi Panel
        @endcan
    </h1>
@endsection

@section('content')

    {{-- Fila: Panel resumen del estudiante --}}
    @can('dashboard.panel.student-summary')
        <div class="row">
            <livewire:dashboard.student-summary />
        </div>
    @endcan

    {{-- Fila: KPI cards (admin/secretaria comparten stats-general; admin agrega grade-books-pending) --}}
    @canany(['dashboard.panel.stats-general', 'dashboard.panel.grade-books-pending', 'dashboard.panel.profesor-stats'])
        <div class="row">
            @can('dashboard.panel.stats-general')
                <livewire:dashboard.stats-general />
            @endcan
            @can('dashboard.panel.grade-books-pending')
                <livewire:dashboard.grade-books-pending />
            @endcan
            @can('dashboard.panel.profesor-stats')
                <livewire:dashboard.profesor-stats />
            @endcan
        </div>
    @endcanany

    {{-- Fila: Gráficos del profesor --}}
    @canany(['dashboard.panel.profesor-grade-books-chart', 'dashboard.panel.profesor-grade-books-summary'])
        <div class="row">
            @can('dashboard.panel.profesor-grade-books-chart')
                <livewire:dashboard.profesor-grade-books-chart />
            @endcan
            @can('dashboard.panel.profesor-grade-books-summary')
                <livewire:dashboard.profesor-grade-books-summary />
            @endcan
        </div>
    @endcanany

    {{-- Panel: cuadros que requieren atención (profesor) --}}
    @can('dashboard.panel.actionable-grade-books')
        <div class="row">
            <livewire:dashboard.actionable-grade-books />
        </div>
    @endcan

    {{-- Fila: Gráficos del admin/secretaria --}}
    @canany(['dashboard.panel.students-by-grade', 'dashboard.panel.grade-books-status'])
        <div class="row">
            @can('dashboard.panel.students-by-grade')
                <livewire:dashboard.students-by-grade-chart />
            @endcan
            @can('dashboard.panel.grade-books-status')
                <livewire:dashboard.grade-books-status-chart />
            @endcan
        </div>
    @endcanany

    {{-- Fila: Tablas de actividad reciente (admin) --}}
    @canany(['dashboard.panel.pending-change-requests', 'dashboard.panel.locked-grade-books'])
        <div class="row">
            @can('dashboard.panel.pending-change-requests')
                <livewire:dashboard.pending-change-requests />
            @endcan
            @can('dashboard.panel.locked-grade-books')
                <livewire:dashboard.locked-grade-books />
            @endcan
        </div>
    @endcanany

    {{-- Fila: Cumpleaños (secretaria) --}}
    @canany(['dashboard.panel.birthday-students', 'dashboard.panel.upcoming-birthdays'])
        <div class="row mb-3">
            @can('dashboard.panel.birthday-students')
                <livewire:dashboard.birthday-students />
            @endcan
            @can('dashboard.panel.upcoming-birthdays')
                <livewire:dashboard.upcoming-birthdays />
            @endcan
        </div>
    @endcanany

@endsection
