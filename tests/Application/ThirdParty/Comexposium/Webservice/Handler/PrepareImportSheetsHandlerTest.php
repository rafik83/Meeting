<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Handler;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ThirdParty\Comexposium\ComexposiumJobQueueInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ComexposiumWebservice;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\PrepareImportSheetsHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\RemoveAlreadyImportedReferences;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class PrepareImportSheetsHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository
            ->findEventWithParameters([Type::TYPE_COMEXPOSIUM_EVENT_REFERENCE])
            ->shouldBeCalled()
            ->willReturn([$event->reveal()])
        ;

        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->shouldBeCalled()->willReturn('111222333');

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType($event->reveal(), Type::TYPE_COMEXPOSIUM_EVENT_REFERENCE)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);
        $comexposiumWebservice
            ->getRegistrationsReference('111222333')
            ->shouldBeCalled()
            ->willReturn(['987654', '7909', '1337'])
        ;

        $comexposiumJobQueue = $this->prophesize(ComexposiumJobQueueInterface::class);
        $comexposiumJobQueue->getRegistrations($event->reveal(), ['987654', '1337'])->shouldBeCalled();

        $removeAlreadyImportedReferences = $this->prophesize(RemoveAlreadyImportedReferences::class);
        $removeAlreadyImportedReferences
            ->handle($event->reveal(), ['987654', '7909', '1337'])
            ->shouldBeCalled()
            ->willReturn(['987654', '1337'])
        ;

        $prepareImportSheetsHandler = new PrepareImportSheetsHandler(
            $eventRepository->reveal(),
            $extraParameterRepository->reveal(),
            $comexposiumWebservice->reveal(),
            $comexposiumJobQueue->reveal(),
            $removeAlreadyImportedReferences->reveal()
        );
        $prepareImportSheetsHandler->handle();
    }
}
