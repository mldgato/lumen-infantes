@extends('adminlte::page')

@section('title', 'Evaluación Psicométrica')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
    <style>
        #psychometric-quill-editor .ql-container {
            min-height: 320px !important;
        }
        #psychometric-quill-editor .ql-editor {
            min-height: 320px !important;
            font-size: .9rem;
        }
    </style>
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-brain mr-2"></i> Evaluación Psicométrica
    </h1>
@endsection

@section('content')
    <livewire:admin.students.admission-psychometric-list />
@endsection

@section('adminlte_js')
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
@stop
