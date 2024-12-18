<div style="text-align: center; margin-top: 2rem;">
    <h3>Subir PDF para Imprimir</h3>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <form action="{{ route('pdf.imprimir') }}" method="POST" enctype="multipart/form-data"
        style="display: inline-block; text-align: left;">
        @csrf

        <!-- Seleccionar archivo PDF -->
        <label for="archivo_pdf" style="display: block; margin-bottom: 0.5rem;">Seleccionar archivo PDF:</label>
        <input type="file" name="archivo_pdf" id="archivo_pdf" accept="application/pdf" required
            style="margin-bottom: 1rem; padding: 0.5rem;">

        <!-- Opciones de impresión -->
        <label for="modo" style="display: block; margin-top: 1rem;">Modo de impresión:</label>
        <select name="modo" id="modo" style="padding: 0.5rem; width: 100%;">
            <option value="color">A Color</option>
            <option value="bn">Blanco y Negro</option>
        </select>

        <label for="paginas" style="display: block; margin-top: 1rem;">Seleccionar páginas:</label>
        <select name="paginas" id="paginas" style="padding: 0.5rem; width: 100%;">
            <option value="todas">Todas las páginas</option>
            <option value="pares">Solo páginas pares</option>
            <option value="impares">Solo páginas impares</option>
            <option value="rango">Rango específico</option>
        </select>

        <!-- Entrada para rango de páginas -->
        <div id="rango-input" style="display: none; margin-top: 1rem;">
            <label for="rango_paginas">Especificar rango (ej: 1-5):</label>
            <input type="text" name="rango_paginas" id="rango_paginas" placeholder="Ej: 1-5"
                style="padding: 0.5rem; width: 100%; border: 1px solid #ccc; border-radius: 5px;">
        </div>

        <!-- Botón de envío -->
        <button type="submit"
            style="margin-top: 1.5rem; padding: 0.5rem 1rem; background-color: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Enviar a Imprimir
        </button>
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
