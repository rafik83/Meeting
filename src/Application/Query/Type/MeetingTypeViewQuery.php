<?php

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingTypeViewQuery
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
     * MeetingTypeViewQuery constructor.
     *
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Sheet $sheet, $locale)
    {
        $this->locale  = $locale;
        $this->sheet   = $sheet;
    }
}
