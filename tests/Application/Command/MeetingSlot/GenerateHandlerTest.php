<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Command\MeetingSlot\Generate;
use Proximum\Vimeet\Application\Command\MeetingSlot\GenerateHandler;
use Proximum\Vimeet\Application\Command\MeetingSlot\GenerateResult;
use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotGenerator;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class GenerateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime();
        $end   = new \DateTime();

        $recipes = [new Recipe($begin, $end, 5, 25)];
        $slots   = [
            new MeetingSlot($event, new \DateTime('+1 days'), new \DateTime('+2 days')),
            new MeetingSlot($event, new \DateTime('+3 days'), new \DateTime('+4 days')),
            new MeetingSlot($event, new \DateTime('+5 days'), new \DateTime('+6 days')),
            new MeetingSlot($event, new \DateTime('+7 days'), new \DateTime('+8 days')),
        ];

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->add($slots[0])->shouldBeCalled();
        $meetingSlotRepository->add($slots[1])->shouldBeCalled();
        $meetingSlotRepository->add($slots[2])->shouldBeCalled();
        $meetingSlotRepository->add($slots[3])->shouldBeCalled();

        $slotGenerator = $this->prophesize(SlotGenerator::class);
        $slotGenerator->generate($event, $recipes)->shouldBeCalled()->willReturn($slots);

        $command = new Generate($event);
        $command->recipes = $recipes;

        $handler = new GenerateHandler($meetingSlotRepository->reveal(), $slotGenerator->reveal());

        $this->assertEquals(new GenerateResult(4), $handler->handle($command));
    }
}
