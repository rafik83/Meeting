<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\HappeningViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\HappeningViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\SpeakerViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\SpeakerViewQueryHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Application\View\Agenda\HappeningView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class HappeningViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $user = UserFactory::create();

        // Data
        $beginHappening1 = new \DateTime('2016-10-12 12:00:00');
        $endHappening1   = new \DateTime('2016-10-12 14:00:00');
        $categoryH1      = new Happening\Category($event, 'Conference', 1, '#123123', '#123123');
        $happening1      = new Happening(
            $event,
            $beginHappening1,
            $endHappening1,
            $categoryH1,
            [],
            false,
            null,
            null,
            true
        );
        $happening1->setTranslation(new Happening\HappeningTranslation($happening1, 'fr', 'title', 'description'));

        $reflection = new \ReflectionClass(Happening::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($happening1, 1);
        $property->setAccessible(false);

        // Expected
        $happeningView1 = new HappeningView(
            1,
            $beginHappening1,
            $endHappening1,
            'title',
            'description',
            [],
            'Conference',
            '#123123',
            '#123123',
            'Europe/Paris',
            null,
            true,
            false
        );

        // Mock
        $speakerViewQueryHandler = $this->prophesize(SpeakerViewQueryHandler::class);
        $speakerViewQueryHandler->handle(new SpeakerViewQuery($happening1, 'fr'))->shouldBeCalled()->willReturn([]);

        $canAccessToWebinar = $this->prophesize(CanAccessToWebinar::class);
        $canAccessToWebinar->isSatisfiableBy($happening1, $user)->shouldBeCalled()->willReturn(false);

        $handler = new HappeningViewQueryHandler(
            $canAccessToWebinar->reveal(),
            $speakerViewQueryHandler->reveal()
        );
        $result = $handler->handle(new HappeningViewQuery(
            $user,
            $happening1,
            $event,
            'fr'
        ));

        $this->assertEquals($happeningView1, $result);
    }
}
