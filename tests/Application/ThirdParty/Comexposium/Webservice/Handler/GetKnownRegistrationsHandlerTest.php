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
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\GetKnownRegistrationsHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Sheet\SheetExtraDataType;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;

class GetKnownRegistrationsHandlerTest extends TestCase
{
    public function testThereIsNotComexposiumEventReferenceInEventExtraParameter()
    {
        $this->expectException(\LogicException::class);

        $event = $this->prophesize(Event::class);
        $sheetExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType(
                $event,
                ExtraParameterType::TYPE_COMEXPOSIUM_EVENT_REFERENCE
            )
            ->willReturn(null)
        ;

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);

        $getKnownRegistrationsHandler = new GetKnownRegistrationsHandler(
            $sheetExtraDataRepository->reveal(),
            $extraParameterRepository->reveal(),
            $comexposiumWebservice->reveal()
        );
        $getKnownRegistrationsHandler->handle($event->reveal());
    }

    public function testThereIsNotComexposiumReferences()
    {
        $event = $this->prophesize(Event::class);
        $sheetExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $sheetExtraDataRepository
            ->getExtraDataByNameAndEvent(
                $event,
                SheetExtraDataType::COMEXPOSIUM_REGISTRATION_REFERENCE
            )
            ->willReturn([])
        ;

        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType(
                $event,
                ExtraParameterType::TYPE_COMEXPOSIUM_EVENT_REFERENCE
            )
            ->willReturn($extraParameter->reveal())
        ;

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);

        $getKnownRegistrationsHandler = new GetKnownRegistrationsHandler(
            $sheetExtraDataRepository->reveal(),
            $extraParameterRepository->reveal(),
            $comexposiumWebservice->reveal()
        );
        $this->assertEquals([], $getKnownRegistrationsHandler->handle($event->reveal()));
    }

    public function testThereIsComexposiumReferences()
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->willReturn(111);
        $extraData1 = $this->prophesize(ExtraData::class);
        $extraData1->getValue()->willReturn(999);
        $extraData1->getSheet()->willReturn($sheet1->reveal());

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->willReturn(222);
        $extraData2 = $this->prophesize(ExtraData::class);
        $extraData2->getValue()->willReturn(888);
        $extraData2->getSheet()->willReturn($sheet2->reveal());

        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getId()->willReturn(333);
        $extraData3 = $this->prophesize(ExtraData::class);
        $extraData3->getValue()->willReturn(777);
        $extraData3->getSheet()->willReturn($sheet3->reveal());

        $event = $this->prophesize(Event::class);
        $sheetExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $sheetExtraDataRepository
            ->getExtraDataByNameAndEvent(
                $event,
                SheetExtraDataType::COMEXPOSIUM_REGISTRATION_REFERENCE
            )
            ->willReturn([$extraData1->reveal(), $extraData2->reveal(), $extraData3->reveal()])
        ;

        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->shouldBeCalled()->willReturn(1337);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType(
                $event,
                ExtraParameterType::TYPE_COMEXPOSIUM_EVENT_REFERENCE
            )
            ->willReturn($extraParameter->reveal())
        ;

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);
        $comexposiumWebservice
            ->getRegistrations(1337, [999, 888])
            ->shouldBeCalled()
            ->willReturn([['sheet1' => 'whatever-data'], ['sheet2' => 'another-data']])
        ;
        $comexposiumWebservice
            ->getRegistrations(1337, [777])
            ->shouldBeCalled()
            ->willReturn([['sheet3' => 'any-data']])
        ;

        $getKnownRegistrationsHandler = new GetKnownRegistrationsHandler(
            $sheetExtraDataRepository->reveal(),
            $extraParameterRepository->reveal(),
            $comexposiumWebservice->reveal()
        );
        $this->assertEquals(
            [['sheet1' => 'whatever-data'], ['sheet2' => 'another-data'], ['sheet3' => 'any-data']],
            $getKnownRegistrationsHandler->handle($event->reveal(), 2)
        );
    }
}
