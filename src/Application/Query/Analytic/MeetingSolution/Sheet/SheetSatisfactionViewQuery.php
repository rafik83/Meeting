<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetSatisfactionViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var int */
    public $numberOfRequest;

    /** @var int */
    public $numberOfMeetings;

    /** @var string */
    public $locale;

    /**
     * @param Sheet  $sheet
     * @param int    $numberOfRequest
     * @param int    $numberOfMeetings
     * @param string $locale
     */
    public function __construct(Sheet $sheet, int $numberOfRequest, int $numberOfMeetings, string $locale)
    {
        $this->sheet = $sheet;
        $this->numberOfRequest = $numberOfRequest;
        $this->numberOfMeetings = $numberOfMeetings;
        $this->locale = $locale;
    }
}
