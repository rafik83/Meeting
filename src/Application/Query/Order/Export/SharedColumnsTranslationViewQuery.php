<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

class SharedColumnsTranslationViewQuery
{
    /** @var string */
    public $adminLocale;

    /**
     * @param string $adminLocale
     */
    public function __construct($adminLocale)
    {
        $this->adminLocale = $adminLocale;
    }
}
