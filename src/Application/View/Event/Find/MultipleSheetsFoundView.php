<?php

namespace Proximum\Vimeet\Application\View\Event\Find;

class MultipleSheetsFoundView
{
    /** @var string */
    public $numero;

    /** @var SheetFoundView[] */
    public $sheets;

    /**
     * @param string           $numero
     * @param SheetFoundView[] $sheetFoundViews
     */
    public function __construct($numero, array $sheetFoundViews)
    {
        $this->numero = $numero;
        $this->sheets = $sheetFoundViews;
    }
}
