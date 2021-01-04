<?php

namespace Proximum\Vimeet\Application\View\Planning\Day;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;

class MeetingView extends AbstractTimeEntityView
{
    /** @var string */
    public $spotRef;

    /** @var ParticipantMetView[] */
    public $participantsMetViews;

    /** @var bool */
    public $hasParticipantsInfo;

    /** @var Sheet */
    public $userSheet;

    /** @var LinkedSheets|null */
    public $sheetMet;

    /**
     * @param \DateTimeInterface   $begin
     * @param \DateTimeInterface   $end
     * @param string               $spotRef
     * @param bool                 $hasParticipantsInfo
     * @param ParticipantMetView[] $participantMetViews
     * @param Sheet|null           $userSheet
     * @param Sheet|null           $sheetMet
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $spotRef,
        bool $hasParticipantsInfo = false,
        array $participantMetViews = [],
        Sheet $userSheet = null,
        Sheet $sheetMet = null
    ) {
        parent::__construct($begin, $end);

        $this->spotRef = $spotRef;
        $this->begin = $begin;
        $this->end = $end;
        $this->participantsMetViews = $participantMetViews;
        $this->hasParticipantsInfo = $hasParticipantsInfo;
        $this->userSheet = $userSheet;
        $this->sheetMet = $sheetMet;
    }
}
