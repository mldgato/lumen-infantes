<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos guardados — {{ config('app.institution_name', 'EduCheck') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: Arial, sans-serif; }
        .public-card {
            max-width: 520px;
            margin: 60px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,.1);
            overflow: hidden;
        }
        .public-header { background-color: #2c3e50; padding: 28px 32px; text-align: center; color: #fff; }
        .public-header h1 { font-size: 20px; margin: 0; }
        .public-header p  { font-size: 13px; color: #bdc3c7; margin: 6px 0 0; }
        .public-body { padding: 40px 32px; text-align: center; }
        .public-footer {
            background-color: #f8f9fa;
            padding: 16px 32px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="public-card">

        <div class="public-header">
            <h1><i class="fas fa-check-circle mr-2"></i>Actualización completada</h1>
            <p>{{ config('app.institution_name', 'EduCheck') }}</p>
        </div>

        <div class="public-body">
            <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
            <h4 class="text-dark mb-3">¡Información guardada exitosamente!</h4>
            <p class="text-muted mb-0">
                Tu información ha sido registrada correctamente.<br>
                Ya puedes cerrar esta página.
            </p>
        </div>

        <div class="public-footer">
            &copy; {{ date('Y') }} {{ config('app.institution_name', 'EduCheck') }}
        </div>

    </div>
</body>

</html>
