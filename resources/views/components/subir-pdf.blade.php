<div class="container">
    <h3>Subir PDF para Imprimir</h3>

    @if (session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p class="error">{{ session('error') }}</p>
    @endif

    <form action="{{ route('pdf.imprimir') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Seleccionar archivo PDF -->
        <label for="archivo_pdf">Seleccionar archivo PDF:</label>
        <input type="file" name="archivo_pdf" id="archivo_pdf" accept="application/pdf" required>

        <!-- Opciones de impresión -->
        <label for="modo">Modo de impresión:</label>
        <select name="modo" id="modo">
            <option value="color">A Color</option>
            <option value="bn">Blanco y Negro</option>
        </select>

        <label for="paginas">Seleccionar páginas:</label>
        <select name="paginas" id="paginas">
            <option value="todas">Todas las páginas</option>
            <option value="pares">Solo páginas pares</option>
            <option value="impares">Solo páginas impares</option>
            <option value="rango">Rango específico</option>
        </select>

        <!-- Entrada para rango de páginas -->
        <div id="rango-input">
            <label for="rango_paginas">Especificar rango (ej: 1-5):</label>
            <input type="text" name="rango_paginas" id="rango_paginas" placeholder="Ej: 1-5">
        </div>

        <!-- Botón de envío -->
        <button type="submit">Enviar a Imprimir</button>
    </form>
</div>

<script>
    document.getElementById('paginas').addEventListener('change', function() {
        const rangoInput = document.getElementById('rango-input');
        if (this.value === 'rango') {
            rangoInput.style.display = 'block';
        } else {
            rangoInput.style.display = 'none';
        }
    });
</script>

<link rel="stylesheet" href="{{ asset('css/pdf.css') }}">
