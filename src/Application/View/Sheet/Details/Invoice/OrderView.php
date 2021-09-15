<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Invoice;

class OrderView
{
    /** @var int */
    public $id;

    /** @var string */
    public $numero;

    /**
     * @param int    $id
     * @param string $numero
     */
    public function __construct($id, $numero)
    {
        $this->id     = $id;
        $this->numero = $numero;
    }
}
