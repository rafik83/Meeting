<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Happening;

use Proximum\Vimeet\Application\Query\Happening\MassUnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Happening\MassUnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Happening\MassUnavailabilityView;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class MassUnavailabilityViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event    = EventFactory::createEvent();
        $category = new Unavailability\Category($event, 'picto', 'title', 'leftColor', 'rightColor');
        $begin    = new \DateTime('2016-10-12 10:00:00');
        $end      = new \DateTime('2016-10-12 12:00:00');
        $mass     = new Unavailability\Mass($event, $category, 'name', $begin, $end, true);
        $mass->createTranslation('fr', 'titre', 'description');

        $reflection = new \ReflectionClass(Unavailability\Mass::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($mass, 1);
        $property->setAccessible(false);

        $handler = new MassUnavailabilityViewQueryHandler();

        $result = $handler->handle(new MassUnavailabilityViewQuery(
            $mass,
            $event,
            'fr'
        ));

        // Expected
        $expected = new MassUnavailabilityView(
            1,
            $begin,
            $end,
            'titre',
            'description',
            'picto',
            'leftColor',
            'rightColor',
            'Europe/Paris',
            true
        );

        $this->assertEquals($expected, $result);
    }
}
