<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event\ExtraParameters;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\ExtraParameters\Update;
use Proximum\Vimeet\Application\Command\Event\ExtraParameters\UpdateHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameters\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameters;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParametersRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $dateTime = new \DateTime('2017-09-10 10:10:10.000');
        $updatedAt = new \DateTime();
        $extraParameter = new ExtraParameters($event->reveal(), Type::TYPE_LENI_USER, 'name', 'value', $dateTime);

        $expected = new ExtraParameters($event->reveal(), Type::TYPE_LENI_USER, 'name', 'value', $dateTime);
        $expected->update('other-name', 'other-value', $updatedAt);

        $extraParameterRepository = $this->prophesize(ExtraParametersRepositoryInterface::class);
        $extraParameterRepository->set($expected)->shouldBeCalled();

        $update = new Update($extraParameter);
        $update->name = 'other-name';
        $update->value = 'other-value';

        $handler = new UpdateHandler($extraParameterRepository->reveal(), $updatedAt);
        $handler->handle($update);
    }
}
