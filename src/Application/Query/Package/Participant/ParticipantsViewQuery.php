<?php

namespace Proximum\Vimeet\Application\Query\Package\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantsViewQuery
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
