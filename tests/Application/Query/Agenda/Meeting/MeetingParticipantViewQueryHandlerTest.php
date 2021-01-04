<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Meeting\MeetingParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Meeting\MeetingParticipantView;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class MeetingParticipantViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $type        = new Type($event);
        $sheet       = SheetFactory::create($event, null, null, $type);
        $participant = ParticipantFactory::create($sheet);
        $cardView    = new CardView(1, false, 'firstName', 'lastName', 'position', 'avatar', false, 1);
        $rules       = [new Rule($event, $type, $type, [], 1)];

        $cardViewQueryHandler = $this->prophesize(CardViewQueryHandler::class);
        $cardViewQueryHandler
            ->handle(new CardViewQuery($participant, 'fr', false, false))
            ->shouldBeCalled()
            ->willReturn($cardView);
        $ruleApplyer          = $this->prophesize(Applyer::class);
        $ruleApplyer->applyRuleForParticipantCard($cardView, $rules)->shouldBeCalled();

        $dDayGuesser = $this->prophesize(DDayGuesser::class);
        $dDayGuesser->isItDDay($event)->shouldBeCalled()->willReturn(true);

        $participantHandler = new MeetingParticipantViewQueryHandler(
            $ruleApplyer->reveal(),
            $cardViewQueryHandler->reveal(),
            $dDayGuesser->reveal()
        );
        $result = $participantHandler->handle(new MeetingParticipantViewQuery($participant, $rules, 'fr'));

        $expected = new MeetingParticipantView($cardView);
        $this->assertEquals($expected, $result);
    }
}
