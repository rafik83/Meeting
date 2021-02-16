<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Planning;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetPlanningViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * Locale of the user to translate the date in the user locale
     *
     * @var string
     */
    public $userLocale;

    /**
     * @var null|Participant
     */
    public $currentParticipant;

    /**
     * @param Sheet            $sheet
     * @param string           $userLocale
     * @param Participant|null $currentParticipant
     */
    public function __construct(Sheet $sheet, $userLocale, Participant $currentParticipant = null)
    {
        $this->sheet              = $sheet;
        $this->userLocale         = $userLocale;
        $this->currentParticipant = $currentParticipant;
    }
}
