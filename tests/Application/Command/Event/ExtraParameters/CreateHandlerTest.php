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
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Event\ExtraParameters\Create;
use Proximum\Vimeet\Application\Command\Event\ExtraParameters\CreateHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameters\Type;
use Proximum\Vimeet\Domain\Exception\Event\ExtraParameters\AnExtraParametersAlreadyExistForThisTypeAndEventException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParametersRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $extraParametersRepository;

    /** @var \DateTime */
    private $dateTime;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->extraParametersRepository = $this->prophesize(ExtraParametersRepositoryInterface::class);
        $this->dateTime = new \DateTime();
    }

    public function testHandleAlreadyExist()
    {
        $otherExtraParameter = $this->prophesize(Event\ExtraParameters::class);
        $this->expectException(AnExtraParametersAlreadyExistForThisTypeAndEventException::class);

        $command = new Create($this->event->reveal());
        $command->type = Type::TYPE_LENI_USER;
        $command->value = 'value';
        $command->name = 'name';

        // Mock
        $this->extraParametersRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_USER)
            ->shouldBeCalled()
            ->willReturn($otherExtraParameter->reveal())
        ;

        $handler = new CreateHandler($this->extraParametersRepository->reveal(), $this->dateTime);
        $handler->handle($command);
    }

    public function testHandle()
    {
        $command = new Create($this->event->reveal());
        $command->type = Type::TYPE_LENI_USER;
        $command->value = 'value';
        $command->name = 'name';

        // Mock
        $this->extraParametersRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_USER)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $expected = new Event\ExtraParameters(
            $this->event->reveal(),
            Type::TYPE_LENI_USER,
            'name',
            'value',
            $this->dateTime
        );
        $this->extraParametersRepository->add($expected)->shouldBeCalled();

        $handler = new CreateHandler($this->extraParametersRepository->reveal(), $this->dateTime);
        $handler->handle($command);
    }
}
