<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;

class RequestViewQuery
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * RequestViewQuery constructor.
     *
     * @param Request $request
     * @param Sheet   $sheet
     * @param string  $locale
     */
    public function __construct(Request $request, Sheet $sheet, $locale)
    {
        $this->request = $request;
        $this->sheet   = $sheet;
        $this->locale  = $locale;
    }
}
