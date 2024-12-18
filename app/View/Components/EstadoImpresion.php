<?php

namespace App\View\Components;

use Illuminate\View\Component;

class EstadoImpresion extends Component
{
    public $estado;

    /**
     * Crear una nueva instancia del componente.
     *
     * @param string $estado
     */
    public function __construct($estado = null)
    {
        $this->estado = $estado;
    }

    /**
     * Renderizar la vista del componente.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.estado-impresion');
    }
}
