<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de Cuenta - Tecno Guard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #f8f9fa;
        }
        .content {
            padding: 20px;
            background-color: #ffffff;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido a Tecno Guard!</h1>
        </div>

        <div class="content">
            <p>Hola,</p>

            <p>Gracias por registrarte en Tecno Guard. Para activar tu cuenta y comenzar a usar nuestros servicios, por favor haz clic en el siguiente botón:</p>

            <div style="text-align: center;">
                <a href="{{ $signedroute }}" class="button">Activar mi cuenta</a>
            </div>

            <p>Si el botón no funciona, puedes copiar y pegar el siguiente enlace en tu navegador:</p>
            <p style="word-break: break-all; color: #666;">{{ $signedroute }}</p>

            <p><strong>Importante:</strong> Este enlace expirará en 10 minutos por razones de seguridad.</p>

            <p>Si no solicitaste esta cuenta, puedes ignorar este correo.</p>
        </div>

        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
            <p>&copy; {{ date('Y') }} Tecno Guard. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
