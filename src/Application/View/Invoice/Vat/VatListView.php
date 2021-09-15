<?php

namespace Proximum\Vimeet\Application\View\Invoice\Vat;

class VatListView
{
    /** @var int in cents */
    public $total;

    /** @var int in cents */
    public $totalWithVat;

    /** @var bool */
    public $vatApplicable;

    /** @var string */
    public $vatMode;

    /** @var VatView[] */
    public $vatViews;

    public function __construct(
        int $total,
        int $totalWithVat,
        bool $vatApplicable,
        string $vatMode,
        array $vatViews = []
    ) {
        $this->total = $total;
        $this->totalWithVat = $totalWithVat;
        $this->vatApplicable = $vatApplicable;
        $this->vatMode = $vatMode;
        $this->vatViews = $vatViews;
    }
}
