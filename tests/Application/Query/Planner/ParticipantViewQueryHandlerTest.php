<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planner;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planner\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Planner\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\Day;
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailabilityView;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Account;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ParticipantViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $event      = EventFactory::createEvent();
        $typeView   = new TypeView(1, 'title');
        $sheetView  = new SheetView(1, $typeView, 2, 2);
        $sheetView2 = new SheetView(2, $typeView, 2, 2);

        $sheet        = SheetFactory::create($event);
        $participant1 = ParticipantFactory::create($sheet);
        $participant1->getUser()->setAccount(new Account())->getAccount()->setFirstName('firstName1');
        $participant1->getUser()->getAccount()->setLastName('lastName1');
        $sheet2       = SheetFactory::create($event);
        $participant2 = ParticipantFactory::create($sheet2);
        $participant3 = ParticipantFactory::create($sheet2);

        $userReflection = new \ReflectionClass(User::class);
        $usedIdProperty   = $userReflection->getProperty('id');
        $usedIdProperty->setAccessible(true);
        $usedIdProperty->setValue($participant1->getUser(), 1);
        $usedIdProperty->setValue($participant2->getUser(), 2);
        $usedIdProperty->setValue($participant3->getUser(), 3);

        $participant2->getUser()->setAccount(new Account())->getAccount()->setFirstName('firstName2');
        $participant2->getUser()->getAccount()->setLastName('lastName2');
        $participant3->getUser()->setAccount(new Account())->getAccount()->setFirstName('firstName3');
        $participant3->getUser()->getAccount()->setLastName('lastName3');

        $dayView   = new Day(1, 12, 10, 2016);
        $slotView  = new SlotView(1, 1, 10, 0, $dayView);
        $slotView2 = new SlotView(2, 2, 11, 30, $dayView);

        $slot  = new MeetingSlot($event, new \DateTime('2016-10-12 10:00:00'), new \DateTime('2016-10-12 10:30:00'));
        $slot2 = new MeetingSlot($event, new \DateTime('2016-10-12 11:30:00'), new \DateTime('2016-10-12 12:00:00'));

        // Reflection
        $reflection = new \ReflectionClass(MeetingSlot::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($slot, 1);
        $property->setValue($slot2, 2);
        $property->setAccessible(false);

        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $propertySheet   = $reflectionSheet->getProperty('id');
        $propertySheet->setAccessible(true);
        $propertySheet->setValue($sheet, 1);
        $propertySheet->setValue($sheet2, 2);
        $propertySheet->setAccessible(false);

        $reflectionParticipant = new \ReflectionClass(Participant::class);
        $propertyParticipant   = $reflectionParticipant->getProperty('id');
        $propertyParticipant->setAccessible(true);
        $propertyParticipant->setValue($participant1, 1);
        $propertyParticipant->setValue($participant2, 2);
        $propertyParticipant->setValue($participant3, 3);
        $propertyParticipant->setAccessible(false);

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $slotRepository        = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $slotAvailability      = $this->prophesize(SlotAvailability::class);

        $participantRepository->getParticipantsBySheetIds([1, 2])->shouldBeCalled()->willReturn(
            [$participant1, $participant2, $participant3]
        );

        $slotRepository->getAvailableSlotByEvent($event)->shouldBeCalled()->willReturn([$slot, $slot2]);
        // Participant 1
        $slotAvailability->getSlotAvailability($slot, $participant1)->shouldBeCalled()->willReturn(
            new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE)
        );

        $slotAvailability->getSlotAvailability($slot2, $participant1)->shouldBeCalled()->willReturn(
            new SlotAvailabilityView(SlotAvailability::UNAVAILABILITY)
        );

        // Participant 2
        $slotAvailability->getSlotAvailability($slot, $participant2)->shouldBeCalled()->willReturn(
            new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE)
        );

        $slotAvailability->getSlotAvailability($slot2, $participant2)->shouldBeCalled()->willReturn(
            new SlotAvailabilityView(SlotAvailability::SLOT_AVAILABLE)
        );

        // Participant 3
        $slotAvailability->getSlotAvailability($slot, $participant3)->shouldBeCalled()->willReturn(
            new SlotAvailabilityView(SlotAvailability::MASS_UNAVAILABILITY)
        );

        $slotAvailability->getSlotAvailability($slot2, $participant3)->shouldBeCalled()->willReturn(
            new SlotAvailabilityView(SlotAvailability::HAPPENING_UNAVAILABILITY)
        );

        $isParticipantVisio = $this->prophesize(IsParticipantVisio::class);
        $isParticipantVisio->isSatisfiedBy($participant1)->willReturn(true);
        $isParticipantVisio->isSatisfiedBy($participant2)->willReturn(true);
        $isParticipantVisio->isSatisfiedBy($participant3)->willReturn(false);

        // Handler
        $handler = new ParticipantViewQueryHandler(
            $participantRepository->reveal(),
            $slotRepository->reveal(),
            $slotAvailability->reveal(),
            $isParticipantVisio->reveal()
        );

        $result = $handler->handle(
            new ParticipantViewQuery($event, [$sheetView, $sheetView2], [$slotView, $slotView2])
        );

        // Expected
        $expected = [
            new ParticipantView(1, 1, 'Firstname1 LASTNAME1', $sheetView, [$slotView2], true),
            new ParticipantView(2, 2, 'Firstname2 LASTNAME2', $sheetView2, [], true),
            new ParticipantView(3, 3, 'Firstname3 LASTNAME3', $sheetView2, [$slotView, $slotView2]),
        ];

        $this->assertEquals($expected, $result);
    }
}
