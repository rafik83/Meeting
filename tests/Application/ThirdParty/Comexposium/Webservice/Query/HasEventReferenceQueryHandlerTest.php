<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Query;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\Exception\EventHasNotComexposiumReferenceException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\GetEventReferenceHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Query\HasEventReferenceQuery;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Query\HasEventReferenceQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;

class HasEventReferenceQueryHandlerTest extends TestCase
{
    public function testHasEventReferenceHandle()
    {
        $event = $this->prophesize(Event::class);
        $eventReferenceHandler = $this->prophesize(GetEventReferenceHandler::class);
        $eventReferenceHandler->handle($event)->shouldBeCalled()->willReturn('whatever');

        $hasEventReferenceQuery = new HasEventReferenceQuery($event->reveal());

        $hasEventReferenceQueryHandler = new HasEventReferenceQueryHandler($eventReferenceHandler->reveal());
        $result = $hasEventReferenceQueryHandler->handle($hasEventReferenceQuery);

        $this->assertTrue($result);
    }

    public function testHasNotEventReferenceHandle()
    {
        $event = $this->prophesize(Event::class);
        $eventReferenceHandler = $this->prophesize(GetEventReferenceHandler::class);
        $eventReferenceHandler->handle($event)->shouldBeCalled()->willThrow(EventHasNotComexposiumReferenceException::class);

        $hasEventReferenceQuery = new HasEventReferenceQuery($event->reveal());

        $hasEventReferenceQueryHandler = new HasEventReferenceQueryHandler($eventReferenceHandler->reveal());
        $result = $hasEventReferenceQueryHandler->handle($hasEventReferenceQuery);

        $this->assertFalse($result);
    }
}
