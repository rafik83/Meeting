<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Handler;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ComexposiumWebservice;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ImportSheetHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class ImportSheetHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $extraParameter = $this->prophesize(ExtraParameter::class);
        $extraParameter->getValue()->shouldBeCalled()->willReturn('999666');

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType($event->reveal(), Type::TYPE_COMEXPOSIUM_EVENT)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;

        $response = [new \stdClass()];

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);
        $comexposiumWebservice->getRegistrations('999666', ['111222333'])->shouldBeCalled()->willReturn($response);

        $importSheetHandler = new ImportSheetHandler(
            $comexposiumWebservice->reveal(),
            $extraParameterRepository->reveal()
        );

        $importSheetHandler->handle($event->reveal(), ['111222333']);
    }
}
