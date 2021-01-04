<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSpotView;
use Proximum\Vimeet\Application\View\Agenda\Admin\SpotView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class MeetingUpdateSpotViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime        = new DateTime();
        $event           = EventFactory::createEvent();
        $type            = new Type($event);
        $fromUser        = UserFactory::create();
        $fromSheet       = new Sheet($event, $type, [], $fromUser, $dateTime);
        $fromParticipant = ParticipantFactory::create($fromSheet, $fromUser);
        $toUser          = UserFactory::create();
        $toSheet         = new Sheet($event, $type, [], $toUser, $dateTime);
        $toParticipant   = ParticipantFactory::create($toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser, $event);
        $slot            = new MeetingSlot($event, new \DateTime(), new \DateTime(), false);
        $spot1           = $this->createSpot(100, $event, 'Box001');
        $spot2           = $this->createSpot(101, $event, 'Box002');

        $meeting = $this->createMeeting(
            3,
            $request,
            $slot,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot1,
            $event,
            false,
            true
        );

        $spotRepository   = $this->prophesize(SpotRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $translator       = $this->prophesize(TranslatorInterface::class);

        $spotRepository->getSpotsForMeeting($meeting, false)->shouldBeCalled()->willReturn([$spot1, $spot2]);

        // Assign 'Box002' spot to 'Whatever company name' Sheet
        $toSheet->setSpot($spot2);
        $sheetInfoGuesser->guessSheetTitle($toSheet)->shouldBeCalled()->willReturn('Whatever company name');

        $meetingUpdateSpotViewQuery        = new MeetingUpdateSpotViewQuery($meeting, false);
        $meetingUpdateSpotViewQueryHandler = new MeetingUpdateSpotViewQueryHandler(
            $spotRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $translator->reveal()
        );

        $meetingUpdateSpotView = $meetingUpdateSpotViewQueryHandler->handle($meetingUpdateSpotViewQuery);

        $expectedMeetingUpdateSpotView = new MeetingUpdateSpotView(3, 100, true, false, [
            new SpotView(100, 'Box001'),
            new SpotView(101, 'Box002 - Whatever company name'),
        ]);

        $this->assertEquals($meetingUpdateSpotView, $expectedMeetingUpdateSpotView);
    }

    /**
     * @param int         $id
     * @param Request     $request
     * @param MeetingSlot $slot
     * @param Sheet       $fromSheet
     * @param array       $fromParticipant
     * @param Sheet       $toSheet
     * @param array       $toParticipant
     * @param DateTime    $dateTime
     * @param Spot        $spot
     * @param Event       $event
     * @param bool        $blockedSpot
     * @param bool        $blockedSlot
     *
     * @return Meeting
     */
    private function createMeeting(
        $id,
        Request $request,
        MeetingSlot $slot,
        Sheet $fromSheet,
        array $fromParticipant,
        Sheet $toSheet,
        array $toParticipant,
        DateTime $dateTime,
        Spot $spot,
        Event $event,
        $blockedSpot,
        $blockedSlot
    ) {
        $meeting = new Meeting(
            $request,
            $slot,
            $fromSheet,
            $fromParticipant,
            $toSheet,
            $toParticipant,
            $dateTime,
            $spot,
            $event,
            $blockedSpot,
            $blockedSlot
        );

        $reflection = new \ReflectionClass(Meeting::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($meeting, $id);
        $property->setAccessible(false);

        return $meeting;
    }

    /**
     * @param int    $id
     * @param Event  $event
     * @param string $reference
     *
     * @return Spot
     */
    private function createSpot($id, Event $event, $reference)
    {
        $spot = SpotFactory::create($event, $reference);

        $reflection = new \ReflectionClass(Spot::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($spot, $id);
        $property->setAccessible(false);

        return $spot;
    }
}
