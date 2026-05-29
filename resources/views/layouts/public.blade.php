<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.institution_name', 'EduCheck'))</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: Arial, sans-serif; }
        .public-card {
            max-width: 520px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,.1);
            overflow: hidden;
        }
        .public-header {
            background-color: #2c3e50;
            padding: 28px 32px;
            text-align: center;
            color: #fff;
        }
        .public-header h1 { font-size: 20px; margin: 0; }
        .public-header p  { font-size: 13px; color: #bdc3c7; margin: 6px 0 0; }
        .public-body { padding: 32px; }
        .public-card-wide {
            max-width: 760px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,.1);
            overflow: hidden;
        }
        .public-footer {
            background-color: #f8f9fa;
            padding: 16px 32px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
        /* Formulario de admisiones */
        .admission-card {
            max-width: 960px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,.12);
            overflow: hidden;
        }
        .admission-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            padding: 28px 32px;
            text-align: center;
            color: #fff;
        }
        .admission-header h1 { font-size: 22px; margin: 0; }
        .admission-header p  { font-size: 13px; color: rgba(255,255,255,.75); margin: 6px 0 0; }
        .admission-logo { max-height: 70px; }
        .admission-body { padding: 28px 32px; }
        .admission-footer {
            background: #f8f9fa;
            padding: 14px 32px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
        fieldset.form-section {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 16px 20px 8px;
        }
        fieldset.form-section legend {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            width: auto;
            padding: 0 8px;
        }
        .req { color: #dc3545; }
    </style>
    @livewireStyles
    @stack('styles')
</head>

<body>
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    @livewireScripts
</body>

</html>
