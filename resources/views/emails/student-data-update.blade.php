<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Datos</title>
</head>

<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#2c3e50;padding:30px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:22px;">{!! $institutionName !!}</h1>
                            <p style="margin:6px 0 0;color:#bdc3c7;font-size:13px;">Sistema de Gestión Escolar</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:40px 40px 20px;">
                            <h2 style="margin:0 0 16px;color:#2c3e50;font-size:20px;">Hola {{ $firstName }},</h2>
                            <p style="margin:0 0 16px;color:#555;font-size:15px;line-height:1.6;">
                                Recibiste este correo porque solicitaste actualizar tus datos personales en
                                <strong>{!! $institutionName !!}</strong>.
                            </p>
                            <p style="margin:0 0 28px;color:#555;font-size:15px;line-height:1.6;">
                                Haz clic en el bot&#243;n de abajo para continuar con el formulario de actualizaci&#243;n:
                            </p>

                            {{-- Button --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom:28px;">
                                        <a href="{{ $url }}"
                                            style="display:inline-block;background-color:#27ae60;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:6px;font-size:15px;font-weight:bold;">
                                            Actualizar mis datos
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 12px;color:#e74c3c;font-size:14px;font-weight:bold;line-height:1.6;">
                                &#9888; Este enlace expira en <strong>60 minutos</strong> y solo puede usarse una vez.
                            </p>
                            <p style="margin:0 0 28px;color:#555;font-size:14px;line-height:1.6;">
                                Si no realizaste esta solicitud, puedes ignorar este correo. Tus datos no
                                ser&#225;n modificados.
                            </p>
                        </td>
                    </tr>

                    {{-- URL fallback --}}
                    <tr>
                        <td style="padding:0 40px 20px;">
                            <div
                                style="background-color:#f8f9fa;border-radius:4px;padding:12px;border-left:4px solid #27ae60;">
                                <p style="margin:0 0 6px;color:#777;font-size:12px;">
                                    Si tienes problemas con el bot&#243;n, copia y pega esta URL en tu navegador:
                                </p>
                                <p style="margin:0;word-break:break-all;font-size:12px;">
                                    <a href="{{ $url }}" style="color:#27ae60;">{{ $url }}</a>
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            style="background-color:#f8f9fa;padding:20px 40px;border-top:1px solid #eee;text-align:center;">
                            <p style="margin:0;color:#999;font-size:12px;">
                                Atentamente, <strong>{!! $institutionName !!}</strong>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
