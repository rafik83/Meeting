<?php

namespace Proximum\Vimeet\Tests\Application\Query\Group\Participant;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Group\Participant\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\Group\Participant\SheetsViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\SheetsViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\ParticipantView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class SheetsViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $manager = UserFactory::create();
        $sheet1  = $this->prophesize(Sheet::class);
        $sheet2  = $this->prophesize(Sheet::class);

        $participant1 = ParticipantFactory::create($sheet1->reveal(), $manager);
        $participant2 = ParticipantFactory::create($sheet1->reveal(), $manager);
        $participant3 = ParticipantFactory::create($sheet2->reveal(), $manager);
        $participant4 = ParticipantFactory::create($sheet2->reveal(), $manager);

        $sheet1->getParticipants()->shouldBeCalled()->willReturn(new ArrayCollection([$participant1, $participant2]));
        $sheet1->getEvent()->shouldBeCalled()->willReturn($event);
        $sheet1->getId()->shouldBeCalled()->willReturn(1);

        $sheet2->getParticipants()->shouldBeCalled()->willReturn(new ArrayCollection([$participant3, $participant4]));
        $sheet2->getEvent()->shouldBeCalled()->willReturn($event);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);

        $day = new Day($event, new \DateTime(), new \DateTime());

        $participantViewQuery1 = new ParticipantViewQuery($participant1, $event, [$day]);
        $participantViewQuery2 = new ParticipantViewQuery($participant2, $event, [$day]);
        $participantViewQuery3 = new ParticipantViewQuery($participant3, $event, [$day]);
        $participantViewQuery4 = new ParticipantViewQuery($participant4, $event, [$day]);
        $participantView1      = new ParticipantView('martin', 'dupont', [$day]);
        $participantView2      = new ParticipantView('louis', 'armand', [$day]);
        $participantView3      = new ParticipantView('laurent', 'zoulou', [$day]);
        $participantView4      = new ParticipantView('benoit', 'artichaut', [$day]);

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetInfoGuesser->guessSheetTitle($sheet1->reveal())->shouldBeCalled()->willReturn('test');
        $sheetInfoGuesser->guessSheetTitle($sheet2->reveal())->shouldBeCalled()->willReturn('atest');

        $participantViewsQueryHandler = $this->prophesize(ParticipantViewQueryHandler::class);
        $participantViewsQueryHandler
            ->handle($participantViewQuery1)
            ->shouldBeCalled()
            ->willReturn($participantView1);

        $participantViewsQueryHandler
            ->handle($participantViewQuery2)
            ->shouldBeCalled()
            ->willReturn($participantView2);

        $participantViewsQueryHandler
            ->handle($participantViewQuery3)
            ->shouldBeCalled()
            ->willReturn($participantView3);

        $participantViewsQueryHandler
            ->handle($participantViewQuery4)
            ->shouldBeCalled()
            ->willReturn($participantView4);

        $handler    = new SheetsViewQueryHandler(
            $sheetInfoGuesser->reveal(),
            $participantViewsQueryHandler->reveal()
        );
        $sheetViews = $handler->handle(new SheetsViewQuery([$sheet1->reveal(), $sheet2->reveal()], [$day]));

        $this->assertEquals($sheetViews[0]->title, 'atest');
        $this->assertEquals($sheetViews[0]->participantViews[0]->lastName, 'artichaut');
    }
}
