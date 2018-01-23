<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Catalog;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class PositionViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $query = new PositionViewQuery($event, 'fr');

        // Expected
        $expectedPositionViews = [
            new PositionView('position1', 'Assistant commercial'),
            new PositionView('position2', 'Assistant export'),
            new PositionView('position3', 'Chef de publicité'),
        ];

        // Mock
        $taggedNomenclatureFilterGetter = $this->prophesize(TaggedNomenclatureFilterGetter::class);

        $taggedNomenclatureFilterGetter->getLastNomenclaturesItems(
            $event,
            Tag::PARTICIPANT_POSITION,
            'fr'
        )->shouldBeCalled()->willReturn([
            "position1" => "Assistant commercial",
            "position2" => "Assistant export",
            "position3" => "Chef de publicité",
        ]);

        $handler        = new PositionViewQueryHandler($taggedNomenclatureFilterGetter->reveal());
        $positionsViews = $handler->handle($query);

        $this->assertEquals($expectedPositionViews, $positionsViews);
    }
}
