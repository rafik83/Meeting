<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Tip\Event;

use Factory\TipFactory;
use Proximum\Vimeet\Application\Command\Tip\Event\Affect;
use Proximum\Vimeet\Application\Command\Tip\Event\AffectHandler;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AffectHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $event = EventFactory::createEvent();
        $tip   = TipFactory::createTip('awesome tip');
        $types = [
            new Type($event),
            new Type($event),
            new Type($event),
        ];

        $command = new Affect();
        $command->tip   = $tip;
        $command->types = $types;

        $handler = new AffectHandler($tipRepository->reveal());

        $tipRepository->setTypes($tip)->shouldBeCalled();

        $handler->handle($command);
    }
}
