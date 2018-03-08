<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Happening\HappeningCategoryView;
use Proximum\Vimeet\Application\View\Happening\HappeningView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class HappeningViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();

        // Data
        $beginHappening1 = new \DateTime('2016-10-12 12:00:00');
        $endHappening1   = new \DateTime('2016-10-12 14:00:00');
        $categoryH1      = new Happening\Category($event, 'Conference', 1, '#123123', '#123123');
        $happening1      = new Happening(
            $event,
            $beginHappening1,
            $endHappening1,
            $categoryH1,
            []
        );
        $happening1->setTranslation(new Happening\HappeningTranslation($happening1, 'fr', 'title', 'description'));

        $reflection = new \ReflectionClass(Happening::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($happening1, 1);
        $property->setAccessible(false);

        // Expected
        $happeningCategoryView = new HappeningCategoryView('title', 'Conference', '#123123', '#123123');
        $happeningView1 = new HappeningView(
            1,
            $happeningCategoryView,
            $beginHappening1,
            $endHappening1,
            'title',
            'description',
            null,
            [],
            'Europe/Paris'
        );

        // Mock
        $happeningCategoryViewQueryHandler = $this->prophesize(CategoryViewQueryHandler::class);
        $happeningCategoryViewQueryHandler->handle(
            new CategoryViewQuery(
                $happening1,
                'fr'
            )
        )->shouldBeCalled()->willReturn($happeningCategoryView);
        $speakerViewQueryHandler = $this->prophesize(SpeakerViewQueryHandler::class);
        $speakerViewQueryHandler->handle(new SpeakerViewQuery($happening1, 'fr'))->shouldBeCalled()->willReturn([]);

        $handler = new HappeningViewQueryHandler(
            $speakerViewQueryHandler->reveal(),
            $happeningCategoryViewQueryHandler->reveal()
        );
        $result = $handler->handle(new HappeningViewQuery(
            $happening1,
            $event,
            'fr'
        ));

        $this->assertEquals($happeningView1, $result);
    }
}
