<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Handler;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ComexposiumWebservice;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\Exception\EventHasNotComexposiumReferenceException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\GetEventReferenceHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\GetKnownRegistrationsHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Sheet\SheetExtraDataType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\ExtraData;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;

class GetKnownRegistrationsHandlerTest extends TestCase
{
    public function testThereIsNotComexposiumEventReferenceInEventExtraParameter()
    {
        $this->expectException(\LogicException::class);

        $event = $this->prophesize(Event::class);
        $sheetExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $getEventReferenceHandler = $this->prophesize(GetEventReferenceHandler::class);
        $getEventReferenceHandler->handle($event->reveal())->willThrow(EventHasNotComexposiumReferenceException::class);

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);

        $getKnownRegistrationsHandler = new GetKnownRegistrationsHandler(
            $comexposiumWebservice->reveal(),
            $sheetExtraDataRepository->reveal(),
            $getEventReferenceHandler->reveal()
        );
        $getKnownRegistrationsHandler->handle($event->reveal());
    }

    public function testThereIsNotComexposiumReferences()
    {
        $event = $this->prophesize(Event::class);
        $sheetExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $sheetExtraDataRepository
            ->getExtraDataByEventAndName(
                $event,
                SheetExtraDataType::COMEXPOSIUM_REGISTRATION_REFERENCE
            )
            ->willReturn([])
        ;

        $getEventReferenceHandler = $this->prophesize(GetEventReferenceHandler::class);
        $getEventReferenceHandler->handle($event->reveal())->willReturn('expected-reference');

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);

        $getKnownRegistrationsHandler = new GetKnownRegistrationsHandler(
            $comexposiumWebservice->reveal(),
            $sheetExtraDataRepository->reveal(),
            $getEventReferenceHandler->reveal()
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
            ->getExtraDataByEventAndName(
                $event,
                SheetExtraDataType::COMEXPOSIUM_REGISTRATION_REFERENCE
            )
            ->willReturn([$extraData1->reveal(), $extraData2->reveal(), $extraData3->reveal()])
        ;

        $getEventReferenceHandler = $this->prophesize(GetEventReferenceHandler::class);
        $getEventReferenceHandler->handle($event->reveal())->willReturn('expected-reference');

        $sheet1data = new \stdClass();
        $sheet1data->reference = 999;
        $sheet2data = new \stdClass();
        $sheet2data->reference = 888;
        $sheet3data = new \stdClass();
        $sheet3data->reference = 777;

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);
        $comexposiumWebservice
            ->getRegistrations('expected-reference', [999, 888])
            ->shouldBeCalled()
            ->willReturn([$sheet1data, $sheet2data])
        ;
        $comexposiumWebservice
            ->getRegistrations('expected-reference', [777])
            ->shouldBeCalled()
            ->willReturn([$sheet3data])
        ;

        $getKnownRegistrationsHandler = new GetKnownRegistrationsHandler(
            $comexposiumWebservice->reveal(),
            $sheetExtraDataRepository->reveal(),
            $getEventReferenceHandler->reveal()
        );
        $this->assertEquals(
            [111 => $sheet1data, 222 => $sheet2data, 333 => $sheet3data],
            $getKnownRegistrationsHandler->handle($event->reveal(), 2)
        );
    }
}
