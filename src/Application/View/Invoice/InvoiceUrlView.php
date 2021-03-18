<?php

namespace Proximum\Vimeet\Application\View\Invoice;

class InvoiceUrlView
{
    /** @var int */
    public $id;

    /** @var string */
    public $number;

    /** @var string */
    public $url;

    /**
     * @param int    $id
     * @param string $number
     * @param string $url
     */
    public function __construct($id, $number, $url)
    {
        $this->id     = $id;
        $this->number = $number;
        $this->url    = $url;
    }
}
