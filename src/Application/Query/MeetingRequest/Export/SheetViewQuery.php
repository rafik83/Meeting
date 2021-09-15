<?php

namespace Proximum\Vimeet\Application\Query\MeetingRequest\Export;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetViewQuery
{
    /** @var string */
    public $locale;

    /** @var Sheet */
    public $sheet;

    /** @var Participant[] */
    public $participants;

    /**
     * @param Sheet  $sheet
     * @param array  $participants
     * @param string $locale
     */
    public function __construct(Sheet $sheet, array $participants, string $locale)
    {
        $this->locale = $locale;
        $this->sheet = $sheet;
        $this->participants = $participants;
    }
}
