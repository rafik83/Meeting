<?php

namespace Proximum\Vimeet\Tests\Domain\Event\PracticalInfo;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\PracticalInfo\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventDuplicated = EventFactory::createEvent('event duplicated');

        $eventDuplicated->setOrganiserName('duplicated name');
        $eventDuplicated->setOrganiserEmail('duplicated email');
        $eventDuplicated->getConfiguration()->updatePracticalInfo(
            'duplicated contact firstname',
            'duplicated contact lastname',
            'duplicated organizer phone',
            'duplicated organizer website'
        );

        $event           = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $eventDuplicated
        );

        $eventRepository->set($event)->shouldBeCalled();

        (new Duplicator($eventRepository->reveal()))->duplicate($event);

        $this->assertEquals('duplicated email', $event->getOrganiserEmail());
        $this->assertEquals('duplicated name', $event->getOrganiserName());
        $this->assertEquals('duplicated contact firstname', $event->getConfiguration()->getContactFirstName());
        $this->assertEquals('duplicated contact lastname', $event->getConfiguration()->getContactLastName());
        $this->assertEquals('duplicated organizer phone', $event->getConfiguration()->getOrganiserPhone());
        $this->assertEquals('duplicated organizer website', $event->getConfiguration()->getOrganiserWebsite());
    }
}
