<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Código QR</title>
    <style>
        .qr-container {
            position: relative;
            display: inline-block;
        }

        .icon-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70px;
            height: 70px;
            border-radius: 90%;
        }
    </style>
</head>

<body>
    {{-- @if (isset($qrCode))
        <h3>Código QR Generado:</h3>
        <div class="qr-container">
            <img src="{{ $qrCode }}" alt="Código QR" style="width: 300px;">
            <img src="{{ $iconPath }}" alt="Ícono" class="icon-overlay">
        </div>
    @endif

    <x-qr /> --}}

    <x-subir-pdf />
</body>

</html>
