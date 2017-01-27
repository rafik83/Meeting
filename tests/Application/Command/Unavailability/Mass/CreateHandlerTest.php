<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Application\Command\Unavailability\Mass\Create;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\CreateHandler;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event    = EventFactory::createEvent();
        $category = new Category($event, 'Conference', 'title', '#123123', '#312312');
        $begin    = new \DateTime('10/10/2016 10:00');
        $end      = new \DateTime('10/10/2016 12:00');

        // Expected
        $expected = new Mass(
            $event,
            $category,
            'name',
            $begin,
            $end,
            true
        );
        $expected->createTranslation('fr', 'titre', 'description');
        $expected->createTranslation('en', 'title', 'description');


        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->create($expected)->shouldBeCalled();

        // Create
        $create               = new Create($event, null);
        $create->category     = $category;
        $create->begin        = $begin;
        $create->end          = $end;
        $create->name         = 'name';
        $create->translations = [
            'fr' => [
                'title'       => 'titre',
                'description' => 'description',
            ],
            'en' => [
                'title'       => 'title',
                'description' => 'description',
            ]
        ];

        // Handler
        $handler = new CreateHandler($massRepository->reveal());
        $handler->handle($create);
    }

    public function testDispatchHandle()
    {
        $event    = EventFactory::createEvent();
        $category = new Category($event, 'Conference', 'title', '#123123', '#312312');
        $begin    = new \DateTime('10/10/2016 10:00');
        $end      = new \DateTime('10/10/2016 12:00');

        // Expected
        $expected = new Mass(
            $event,
            $category,
            'name',
            $begin,
            $end,
            true,
            true,
            [
                ['from' => new \DateTime('10/10/2016 10:00'), 'to' => new \DateTime('10/10/2016 11:00')],
                ['from' => new \DateTime('10/10/2016 11:00'), 'to' => new \DateTime('10/10/2016 12:00')],
            ]
        );
        $expected->createTranslation('fr', 'titre', 'description');
        $expected->createTranslation('en', 'title', 'description');

        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->create($expected)->shouldBeCalled();

        // Create
        $create               = new Create($event, null);
        $create->category     = $category;
        $create->begin        = $begin;
        $create->end          = $end;
        $create->name         = 'name';
        $create->translations = [
            'fr' => [
                'title'       => 'titre',
                'description' => 'description',
            ],
            'en' => [
                'title'       => 'title',
                'description' => 'description',
            ]
        ];

        $create->dispatch = true;
        $create->timeSlots = [
            ['from' => new \DateTime('10/10/2016 10:00'), 'to' => new \DateTime('10/10/2016 11:00')],
            ['from' => new \DateTime('10/10/2016 11:00'), 'to' => new \DateTime('10/10/2016 12:00')],
        ];

        // Handler
        $handler = new CreateHandler($massRepository->reveal());
        $handler->handle($create);
    }
}
