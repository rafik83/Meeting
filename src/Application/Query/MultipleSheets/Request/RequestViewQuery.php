<?php

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;

class RequestViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var Request */
    public $request;

    /** @var string */
    public $locale;

    /**
     * @param Sheet   $sheet
     * @param Request $request
     * @param string  $locale
     */
    public function __construct(Sheet $sheet, Request $request, $locale)
    {
        $this->sheet = $sheet;
        $this->request = $request;
        $this->locale = $locale;
    }
}
