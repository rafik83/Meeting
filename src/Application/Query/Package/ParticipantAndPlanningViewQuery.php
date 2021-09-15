<?php

namespace Proximum\Vimeet\Application\Query\Package;

use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantAndPlanningViewQuery
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Sheet $sheet, $locale)
    {
        $this->locale = $locale;
        $this->sheet  = $sheet;
    }
}
