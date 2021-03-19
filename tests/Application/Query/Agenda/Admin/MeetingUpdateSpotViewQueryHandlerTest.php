<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsQuery;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsQueryHandler;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsView;
use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSpotView;
use Proximum\Vimeet\Application\View\Agenda\Admin\ParticipantView;
use Proximum\Vimeet\Application\View\Agenda\Admin\SpotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\SlotView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
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
        $fromParticipant1 = $this->prophesize(Participant::class);
        $fromParticipant1->getId()->shouldBeCalled()->willReturn(7);
        $fromParticipant1->getFullname()->shouldBeCalled()->willReturn('Dupont');
        $fromParticipant2 = $this->prophesize(Participant::class);
        $fromParticipant2->getId()->shouldBeCalled()->willReturn(8);
        $fromParticipant2->getFullname()->shouldBeCalled()->willReturn('Martin');
        $fromSheet->addParticipant($fromParticipant1->reveal());
        $fromSheet->addParticipant($fromParticipant2->reveal());
        $toUser          = UserFactory::create();
        $toSheet         = new Sheet($event, $type, [], $toUser, $dateTime);
        $toParticipant   = ParticipantFactory::create($toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser, $event);
        $currentSlot = $this->prophesize(MeetingSlot::class);
        $currentSlot->getId()->shouldBeCalled()->willReturn(1);
        $currentSlot->getEvent()->willReturn($event);
        $timezone = new \DateTimeZone('Europe/Paris');
        $currentSlot->getBegin()->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-03-08 15:00:00', $timezone));
        $currentSlot->getEnd()->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-03-08 15:15:00', $timezone));
        $otherSlot1 = $this->prophesize(MeetingSlot::class);
        $otherSlot1->getId()->willReturn(2);
        $otherSlot1->getEvent()->willReturn($event);
        $otherSlot1->getBegin()->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-03-08 15:20:00', $timezone));
        $otherSlot1->getEnd()->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-03-08 15:35:00', $timezone));
        $otherSlot2 = $this->prophesize(MeetingSlot::class);
        $otherSlot2->getId()->willReturn(3);
        $otherSlot2->getEvent()->willReturn($event);
        $otherSlot2->getBegin()->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-03-08 15:40:00', $timezone));
        $otherSlot2->getEnd()->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-03-08 15:55:00', $timezone));
        $spot1           = $this->createSpot(100, $event, 'Box001');
        $spot2           = $this->createSpot(101, $event, 'Box002');

        $meeting = $this->createMeeting(
            3,
            $request,
            $currentSlot->reveal(),
            $fromSheet,
            [$fromParticipant1->reveal(), $fromParticipant2->reveal()],
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
        $translator->trans('form.update_meeting.children.meetingSlot.label.begin.end', ['%day%' => 'lundi 8 mars 2021', '%begin%' => '15:00', '%end%' => '15:15'], 'forms', 'fr')->willReturn('current slot');
        $translator->trans('form.update_meeting.children.meetingSlot.label.begin.end', ['%day%' => 'lundi 8 mars 2021', '%begin%' => '15:20', '%end%' => '15:35'], 'forms', 'fr')->willReturn('other slot 1');
        $translator->trans('form.update_meeting.children.meetingSlot.label.begin.end', ['%day%' => 'lundi 8 mars 2021', '%begin%' => '15:40', '%end%' => '15:55'], 'forms', 'fr')->willReturn('other slot 2');

        $getAvailableSlotsQueryHandler = $this->prophesize(GetAvailableSlotsQueryHandler::class);
        $getAvailableSlotsQueryHandler->handle(new GetAvailableSlotsQuery($meeting, false, $fromSheet, false))
            ->willReturn(new GetAvailableSlotsView([$currentSlot->reveal(), $otherSlot1->reveal(), $otherSlot2->reveal()], [7=>[1,2], 8=>[2,3]]));

        $spotRepository->getSpotsForSlotAndParticipantsQuantity($currentSlot, 2, $meeting, $fromSheet, $toSheet, false)
            ->willReturn([$spot1, $spot2]);

        $spotRepository->getSpotsForSlotAndParticipantsQuantity($otherSlot1, 2, $meeting, $fromSheet, $toSheet, false)
            ->willReturn([$spot1]);

        $spotRepository->getSpotsForSlotAndParticipantsQuantity($otherSlot2, 2, $meeting, $fromSheet, $toSheet, false)
            ->willReturn([$spot2]);

        $spotRepository->getActiveByEvent($event)->willReturn([$spot1, $spot2]);

        // Assign 'Box002' spot to 'Whatever company name' Sheet
        $toSheet->setSpot($spot2);
        $sheetInfoGuesser->guessSheetTitle($toSheet)->shouldBeCalled()->willReturn('Whatever company name');

        $meetingUpdateSpotViewQuery        = new MeetingUpdateSpotViewQuery($meeting, $fromSheet, false, 'fr');
        $meetingUpdateSpotViewQueryHandler = new MeetingUpdateSpotViewQueryHandler(
            $spotRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $translator->reveal(),
            $getAvailableSlotsQueryHandler->reveal()
        );

        $meetingUpdateSpotView = $meetingUpdateSpotViewQueryHandler->handle($meetingUpdateSpotViewQuery);

        $expectedMeetingUpdateSpotView = new MeetingUpdateSpotView(
            3,
            100,
            true,
            false,
            [
                new SpotView(100, 'Box001', 4, [1, 2]),
                new SpotView(101, 'Box002 - Whatever company name', 4, [1, 3]),
            ],
            [new ParticipantView(7, 'Dupont'), new ParticipantView(8, 'Martin')],
            [7, 8],
            [new SlotView(1, 'current slot'), new SlotView(2, 'other slot 1'), new SlotView(3, 'other slot 2')],
            [7 => [1, 2], 8 => [2, 3]],
            1,
            1
        );

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
