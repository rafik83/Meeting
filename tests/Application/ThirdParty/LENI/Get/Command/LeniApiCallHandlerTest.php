<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Command;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCallHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class LeniApiCallHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event1 = $this->prophesize(Event::class);
        $event2 = $this->prophesize(Event::class);

        $leniApi = $this->prophesize(LeniApiCaller::class);
        $leniApi->get($event1->reveal())->shouldBeCalled();
        $leniApi->get($event2->reveal())->shouldBeCalled();

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository
            ->findEventWithParameters([Type::TYPE_LENI_USER, Type::TYPE_LENI_EVENT])
            ->shouldBeCalled()
            ->willReturn([$event1->reveal(), $event2->reveal()])
        ;

        $leniApiCallHandler = new LeniApiCallHandler($leniApi->reveal(), $eventRepository->reveal());
        $leniApiCallHandler->handle(new LeniApiCall());
    }
}
