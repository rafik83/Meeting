<?php

namespace Proximum\Vimeet\Application\View\Package\Vat;

class VatListView
{
    /** @var int */
    public $total;

    /** @var int */
    public $totalWithVat;

    /** @var bool */
    public $vatApplicable;

    /** @var string */
    public $vatMode;

    /** @var VatView[] */
    public $vatViews;

    /**
     * @param int    $total         in cents
     * @param int    $totalWithVat  in cents
     * @param bool   $vatApplicable
     * @param string $vatMode
     * @param array  $vatViews
     */
    public function __construct(
        int $total,
        int $totalWithVat,
        bool $vatApplicable,
        string $vatMode,
        array $vatViews = []
    ) {
        $this->total = $total;
        $this->totalWithVat = $totalWithVat;
        $this->vatViews = $vatViews;
        $this->vatApplicable = $vatApplicable;
        $this->vatMode = $vatMode;
    }

    /**
     * @return int in cents
     */
    public function getVatAmount(): int
    {
        return $this->totalWithVat - $this->total;
    }
}
