<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Request;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingRequestListViewQuery implements Query
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Sheet $sheet, $locale)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
    }
}
