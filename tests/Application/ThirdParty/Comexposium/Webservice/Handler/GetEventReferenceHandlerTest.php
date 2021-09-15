<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Handler;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\Exception\EventHasNotComexposiumReferenceException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\GetEventReferenceHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class GetEventReferenceHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->willReturn('expected-reference');

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType(
                $event->reveal(),
                ExtraParameterType::TYPE_COMEXPOSIUM_EVENT_REFERENCE
            )
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;

        $getEventReferenceHandler = new GetEventReferenceHandler($extraParameterRepository->reveal());
        $this->assertEquals(
            'expected-reference',
            $getEventReferenceHandler->handle($event->reveal())
        );
    }

    public function testEventHasNotComexposiumReferenceException()
    {
        $this->expectException(EventHasNotComexposiumReferenceException::class);

        $event = $this->prophesize(Event::class);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType(
                $event->reveal(),
                ExtraParameterType::TYPE_COMEXPOSIUM_EVENT_REFERENCE
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $getEventReferenceHandler = new GetEventReferenceHandler($extraParameterRepository->reveal());
        $getEventReferenceHandler->handle($event->reveal());
    }
}
