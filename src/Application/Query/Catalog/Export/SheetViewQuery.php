<?php

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var Sheet */
    public $viewer;

    /** @var string */
    public $locale;

    /** @var string */
    public $fallback;

    /** @var bool */
    public $isTypeColumn;

    /**
     * @param Sheet  $sheet
     * @param Sheet  $viewer
     * @param string $locale
     * @param string $fallback
     * @param bool   $isTypeColumn
     */
    public function __construct(
        Sheet $sheet,
        Sheet $viewer,
        string $locale,
        string $fallback,
        bool $isTypeColumn
    ) {
        $this->sheet = $sheet;
        $this->viewer = $viewer;
        $this->locale = $locale;
        $this->fallback = $fallback;
        $this->isTypeColumn = $isTypeColumn;
    }
}
