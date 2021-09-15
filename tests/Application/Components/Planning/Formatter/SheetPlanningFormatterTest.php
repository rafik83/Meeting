<?php

namespace Proximum\Vimeet\Tests\Application\Components\Planning\Formatter;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\Planning\Formatter\SheetPlanningFormatter;
use Proximum\Vimeet\Application\Components\Planning\Formatter\UnallocatedFormatter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class SheetPlanningFormatterTest extends TestCase
{
    public function testFormat()
    {
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $locale = 'fr';
        $firstParticipantToDisplay = $this->prophesize(Participant::class);
        $secondParticipant = $this->prophesize(Participant::class);
        $firstParticipantToDisplay->getSheet()->willReturn($sheet->reveal());
        $secondParticipant->getSheet()->willReturn($sheet->reveal());
        $sheet->getEvent()->willReturn($event->reveal());
        $firstParticipantToDisplay->getUser()->willReturn($user1->reveal());
        $secondParticipant->getUser()->willReturn($user2->reveal());
        $sheet
            ->getParticipants()
            ->willReturn(new ArrayCollection([$secondParticipant->reveal(), $firstParticipantToDisplay->reveal()]));

        // Mock
        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $unallocatedFormatter = $this->prophesize(UnallocatedFormatter::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser
            ->guessParticipantCompleteName(
                $firstParticipantToDisplay->reveal(),
                $locale
            )->shouldBeCalled()
            ->willReturn('First Participant Name 1');
        $participantInfoGuesser
            ->guessParticipantCompleteName(
                $secondParticipant->reveal(),
                $locale
            )->shouldBeCalled()
            ->willReturn('Second Participant Name 2');
        $participantPlanningFormatter
            ->formatPlanningFromUserAndEvent($user1->reveal(), $event->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn("Planning user 1\nfake value\n");
        $participantPlanningFormatter
            ->formatPlanningFromUserAndEvent($user2->reveal(), $event->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn("Planning user 2\nother fake value\n");

        // Method to test
        $sheetPlanningFormatter = new SheetPlanningFormatter(
            $participantPlanningFormatter->reveal(),
            $unallocatedFormatter->reveal(),
            $participantInfoGuesser->reveal()
        );
        $result = $sheetPlanningFormatter->format($sheet->reveal(), $locale, $firstParticipantToDisplay->reveal());

        $expected = "## First Participant Name 1\n\nPlanning user 1\nfake value\n\n\n## Second Participant Name 2\n\nPlanning user 2\nother fake value\n\n\n";

        $this->assertEquals($expected, $result);
    }
}
