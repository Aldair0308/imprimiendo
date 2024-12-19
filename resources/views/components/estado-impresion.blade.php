{{-- @if ($estado)
    <p
        class="
        @if ($estado === 'impresion en proceso') success
        @elseif ($estado === 'impresion en la lista de espera') warning
        @elseif ($estado === 'se esta imprimiendo') info
        @elseif ($estado === 'gracias por su paciencia') primary
        @else error @endif
    ">
        {{ ucfirst($estado) }}
    </p>
@endif --}}
