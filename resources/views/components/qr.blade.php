<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Código QR</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        h1 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            text-align: center;
        }

        form {
            width: 100%;
            max-width: 500px;
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        label {
            font-size: 1rem;
            color: #555;
        }

        input[type="text"] {
            padding: 0.5rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        button {
            padding: 0.75rem;
            font-size: 1rem;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        h3 {
            margin-top: 2rem;
            font-size: 1.2rem;
            color: #333;
        }

        img {
            max-width: 100%;
            height: auto;
            margin-top: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Responsive */
        @media (max-width: 600px) {
            h1 {
                font-size: 1.5rem;
            }

            form {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <h1>Generador de Código QR</h1>

    <!-- Formulario para ingresar el texto -->
    <form action="{{ route('generar.qr') }}" method="POST">
        @csrf
        <label for="texto">Texto para el QR:</label>
        <input type="text" id="texto" name="texto" placeholder="Ingresa el texto" required>
        <button type="submit">Generar QR</button>
    </form>

    <!-- Mostrar el código QR si existe -->
    @if (isset($qrCode))
        <h3>Código QR Generado:</h3>
        <img src="{{ $qrCode }}" alt="Código QR">
    @endif
</body>

</html>
