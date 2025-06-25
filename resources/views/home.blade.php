<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticación Completada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f2f5;
            margin: 0;
            text-align: center;
        }
        .message-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
        }
        h1 {
            color: #28a745;
            margin-bottom: 20px;
        }
        p {
            color: #333;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <h1>¡Autenticación Completada!</h1>
        <p>Estás siendo redirigido a la aplicación principal...</p>
        @if(isset($redirect_url))
        <p>Si la redirección no ocurre automáticamente, haz clic <a href="{{ $redirect_url }}">aquí</a>.</p>
        @endif
    </div>

    <script>
        @if(isset($redirect_url))
        setTimeout(function() {
            window.location.href = '{{ $redirect_url }}';
        }, 3000);
        @endif
    </script>
</body>
</html>
