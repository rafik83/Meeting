<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Domain\Model\Sheet;

class BillingInfoViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $adminLocale;

    /**
     * @param Sheet  $sheet
     * @param string $adminLocale
     */
    public function __construct(Sheet $sheet, $adminLocale)
    {
        $this->sheet       = $sheet;
        $this->adminLocale = $adminLocale;
    }
}
