<?php

namespace Proximum\Vimeet\Tests\Application\Command\MeetingSlot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\MeetingSlot\Generate;
use Proximum\Vimeet\Application\Command\MeetingSlot\GenerateHandler;
use Proximum\Vimeet\Application\Command\MeetingSlot\GenerateResult;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Slot\GeneratedEvent;
use Proximum\Vimeet\Application\Exception\Slot\SlotOutOfDayException;
use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotGenerator;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class GenerateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $day = new Day($event, new \DateTime('2017-10-10 10:00:00.000'), new \DateTime('2017-10-10 18:00:00.000'));
        $event->setDays([$day]);
        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end   = new \DateTime('2017-10-10 12:00:00.000');

        $recipes = [new Recipe($begin, $end, 5, 25)];
        $slots   = [
            new MeetingSlot($event, new \DateTime('2017-10-10 10:00:00.000'), new \DateTime('2017-10-10 10:25:00.000')),
            new MeetingSlot($event, new \DateTime('2017-10-10 10:30:00.000'), new \DateTime('2017-10-10 10:55:00.000')),
            new MeetingSlot($event, new \DateTime('2017-10-10 11:00:00.000'), new \DateTime('2017-10-10 11:25:00.000')),
            new MeetingSlot($event, new \DateTime('2017-10-10 11:30:00.000'), new \DateTime('2017-10-10 11:55:00.000')),
        ];

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->add($slots[0])->shouldBeCalled();
        $meetingSlotRepository->add($slots[1])->shouldBeCalled();
        $meetingSlotRepository->add($slots[2])->shouldBeCalled();
        $meetingSlotRepository->add($slots[3])->shouldBeCalled();

        $slotGenerator = $this->prophesize(SlotGenerator::class);
        $slotGenerator->generate($event, $recipes)->shouldBeCalled()->willReturn($slots);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::SLOT_GENERATED, new GeneratedEvent($event))->shouldBeCalled();

        $command = new Generate($event);
        $command->recipes = $recipes;

        $handler = new GenerateHandler(
            $meetingSlotRepository->reveal(),
            $slotGenerator->reveal(),
            $eventDispatcher->reveal()
        );

        $this->assertEquals(new GenerateResult(4), $handler->handle($command));
    }

    public function testHandleSlotOutOfDay()
    {
        $this->expectException(SlotOutOfDayException::class);
        $event = EventFactory::createEvent();
        $day = new Day($event, new \DateTime('2017-10-10 10:00:00.000'), new \DateTime('2017-10-10 11:00:00.000'));
        $event->setDays([$day]);
        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end   = new \DateTime('2017-10-10 12:00:00.000');

        $recipes = [new Recipe($begin, $end, 5, 25)];
        $slots   = [
            new MeetingSlot($event, new \DateTime('2017-10-10 10:00:00.000'), new \DateTime('2017-10-10 10:25:00.000')),
            new MeetingSlot($event, new \DateTime('2017-10-10 10:30:00.000'), new \DateTime('2017-10-10 10:55:00.000')),
            new MeetingSlot($event, new \DateTime('2017-10-10 11:00:00.000'), new \DateTime('2017-10-10 11:25:00.000')),
            new MeetingSlot($event, new \DateTime('2017-10-10 11:30:00.000'), new \DateTime('2017-10-10 11:55:00.000')),
        ];

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->add($slots[0])->shouldBeCalled();
        $meetingSlotRepository->add($slots[1])->shouldBeCalled();
        $meetingSlotRepository->add($slots[2])->shouldNotBeCalled();
        $meetingSlotRepository->add($slots[3])->shouldNotBeCalled();

        $slotGenerator = $this->prophesize(SlotGenerator::class);
        $slotGenerator->generate($event, $recipes)->shouldBeCalled()->willReturn($slots);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::SLOT_GENERATED, new GeneratedEvent($event))->shouldNotBeCalled();

        $command = new Generate($event);
        $command->recipes = $recipes;

        $handler = new GenerateHandler(
            $meetingSlotRepository->reveal(),
            $slotGenerator->reveal(),
            $eventDispatcher->reveal()
        );

        $this->assertEquals(new GenerateResult(4), $handler->handle($command));
    }
}
