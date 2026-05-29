@extends('layouts.public')

@section('title', 'Proceso de Admisiones — ' . config('app.institution_name', 'EduCheck'))

@section('content')
    <livewire:admission-form />
@endsection
