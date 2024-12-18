<div style="text-align: center; margin-top: 2rem;">
    <h3>Subir PDF para Imprimir</h3>
    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <form action="{{ route('pdf.imprimir') }}" method="POST" enctype="multipart/form-data" style="display: inline-block; text-align: left;">
        @csrf
        <label for="archivo_pdf" style="display: block; margin-bottom: 0.5rem;">Seleccionar archivo PDF:</label>
        <input type="file" name="archivo_pdf" id="archivo_pdf" accept="application/pdf" required
               style="margin-bottom: 1rem; padding: 0.5rem;">
        <button type="submit" style="padding: 0.5rem 1rem; background-color: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Enviar a Imprimir
        </button>
    </form>
</div>
