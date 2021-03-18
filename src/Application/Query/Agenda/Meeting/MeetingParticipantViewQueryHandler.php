<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Meeting;

use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingParticipantView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Rule\Applyer;

class MeetingParticipantViewQueryHandler
{
    /** @var Applyer */
    private $ruleApplyer;

    /** @var CardViewQueryHandler */
    private $cardViewQueryHandler;

    /** @var DDayGuesser */
    private $dDayGuesser;

    public function __construct(
        Applyer $ruleApplyer,
        CardViewQueryHandler $cardViewQueryHandler,
        DDayGuesser $dDayGuesser
    ) {
        $this->ruleApplyer = $ruleApplyer;
        $this->cardViewQueryHandler = $cardViewQueryHandler;
        $this->dDayGuesser = $dDayGuesser;
    }

    public function handle(MeetingParticipantViewQuery $query): MeetingParticipantView
    {
        $event = $query->participant->getSheet()->getEvent();
        $isDDay = $this->dDayGuesser->isItDDay($event);
        $getCheckinStatus = $isDDay && $event->accessControlEnabledAndShowCheckinStatus();

        $card = $this->cardViewQueryHandler->handle(
            new CardViewQuery($query->participant, $query->locale, false, $getCheckinStatus)
        );
        $this->ruleApplyer->applyRuleForParticipantCard($card, $query->rules);

        return new MeetingParticipantView($card);
    }
}
