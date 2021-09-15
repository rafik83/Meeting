<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSlotView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class MeetingUpdateSlotViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime        = new DateTime();
        $event           = EventFactory::createEvent();
        $type            = new Type($event);
        $fromUser        = UserFactory::create();
        $fromSheet       = new Sheet($event, $type, [], $fromUser, $dateTime);
        $fromParticipant = $this->createParticipant(88, $fromSheet, $fromUser);
        $toUser          = UserFactory::create();
        $toSheet         = new Sheet($event, $type, [], $toUser, $dateTime);
        $toParticipant   = $this->createParticipant(93, $toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser, $event);

        $slot1 = SlotFactory::createSlot(
            9,
            $event,
            new \DateTime('2017-01-01 08:00:00'),
            new \DateTime('2017-01-01 09:00:00')
        );

        $slot2 = SlotFactory::createSlot(
            99,
            $event,
            new \DateTime('2017-01-01 09:00:00'),
            new \DateTime('2017-01-01 10:00:00')
        );

        $meeting = $this->createMeeting(
            3,
            $request,
            $slot1,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            new Spot('Stand', $event, 1, 1, 1, true),
            $event,
            false,
            true
        );

        $spotRepository   = $this->prophesize(SpotRepositoryInterface::class);
        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants(
                $event,
                [$fromParticipant, $toParticipant],
                false,
                $meeting
            )
            ->shouldBeCalled()
            ->willReturn([$slot1, $slot2]);

        $spotRepository
            ->hasSpotsForSlotAndParticipantsQuantity($slot1, 2, $meeting, $fromSheet, $toSheet, true)
            ->shouldBeCalled()
            ->willReturn(true);

        $spotRepository
            ->hasSpotsForSlotAndParticipantsQuantity($slot2, 2, $meeting, $fromSheet, $toSheet, true)
            ->shouldBeCalled()
            ->willReturn(true);

        $meetingUpdateSpotViewQuery        = new MeetingUpdateSlotViewQuery($meeting, true);
        $meetingUpdateSpotViewQueryHandler = new MeetingUpdateSlotViewQueryHandler(
            $spotRepository->reveal(),
            $meetingSlotRepository->reveal()
        );

        $meetingUpdateSpotView = $meetingUpdateSpotViewQueryHandler->handle($meetingUpdateSpotViewQuery);

        $expectedMeetingUpdateSlotView = new MeetingUpdateSlotView([9, 99]);

        $this->assertEquals($meetingUpdateSpotView, $expectedMeetingUpdateSlotView);
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
     * @param int   $id
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return Participant
     */
    private function createParticipant($id, $sheet, $user)
    {
        $participant = ParticipantFactory::create($sheet, $user);

        $reflection = new \ReflectionClass(Participant::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, $id);
        $property->setAccessible(false);

        return $participant;
    }
}
