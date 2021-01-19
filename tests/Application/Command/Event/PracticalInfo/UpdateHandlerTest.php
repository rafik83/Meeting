<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\PracticalInfo;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\PracticalInfo\Update;
use Proximum\Vimeet\Application\Command\Event\PracticalInfo\UpdateHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setOrganiserName('baseOrganiserName')->setOrganiserEmail('baseOrganiserEmail');
        $event
            ->getConfiguration()
            ->updatePracticalInfo('baseContactFirstName', 'baseContactLastName', 'basePhone', 'baseWebsite');

        $update                   = new Update($event);
        $update->organiserName    = 'newOrganiserName';
        $update->organiserEmail   = 'newOrganiserEmail';
        $update->organiserPhone   = 'newPhone';
        $update->organiserWebsite = 'newWebsite';
        $update->contactLastName  = 'newContactLastName';
        $update->contactFirstName = 'newContactFirstName';

        $expectedEvent = EventFactory::createEvent();
        $expectedEvent->setOrganiserEmail('newOrganiserEmail')->setOrganiserName('newOrganiserName');
        $expectedEvent
            ->getConfiguration()
            ->updatePracticalInfo('newContactFirstName', 'newContactLastName', 'newPhone', 'newWebsite');

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        $handler = new UpdateHandler($eventRepository->reveal());
        $handler->handle($update);
    }
}
