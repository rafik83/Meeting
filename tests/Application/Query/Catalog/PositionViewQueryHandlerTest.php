<?php

namespace Proximum\Vimeet\Tests\Application\Query\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Domain\Catalog\NomenclaturesItemsView;
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;
use Proximum\Vimeet\Tests\Factory\EventFactory;

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

        $taggedNomenclatureFilterGetter
            ->getLastNomenclaturesItems(
                $event,
                Tag::PARTICIPANT_POSITION,
                'fr'
            )
            ->shouldBeCalled()
            ->willReturn(
                new NomenclaturesItemsView(
                    [
                        'position1' => 'Assistant commercial',
                        'position2' => 'Assistant export',
                        'position3' => 'Chef de publicité',
                    ], 1
                )
            )
        ;

        $handler        = new PositionViewQueryHandler($taggedNomenclatureFilterGetter->reveal());
        $positionsViews = $handler->handle($query);

        $this->assertEquals($expectedPositionViews, $positionsViews);
    }
}
