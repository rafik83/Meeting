<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event\ExtraParameters;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\ExtraParameters\Remove;
use Proximum\Vimeet\Application\Command\Event\ExtraParameters\RemoveHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameters\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameters;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParametersRepositoryInterface;

class RemoveHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event = $this->prophesize(Event::class);

        $extraParameter = new ExtraParameters($event->reveal(), Type::TYPE_LENI_USER, 'name', 'value', $dateTime);

        // Mock
        $extraParameterRepository = $this->prophesize(ExtraParametersRepositoryInterface::class);
        $extraParameterRepository->remove($extraParameter)->shouldBeCalled();

        // Command
        $remove = new Remove($extraParameter);

        //Handler
        $handler = new RemoveHandler($extraParameterRepository->reveal());
        $handler->handle($remove);
    }
}
