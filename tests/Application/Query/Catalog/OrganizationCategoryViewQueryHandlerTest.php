<?php

namespace Proximum\Vimeet\Tests\Application\Query\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQueryHandler;
use Proximum\Vimeet\Domain\Catalog\NomenclaturesItemsView;
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class OrganizationCategoryViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $query = new OrganizationCategoryViewQuery($event, 'fr');

        // Expected
        $expectedCategoriesViews = [
            new OrganizationCategoryView('category8', 'Association'),
            new OrganizationCategoryView('category5', 'ETI'),
            new OrganizationCategoryView('category10', 'Expert'),
        ];

        // Mock
        $taggedNomenclatureFilterGetter = $this->prophesize(TaggedNomenclatureFilterGetter::class);

        $taggedNomenclatureFilterGetter
            ->getLastNomenclaturesItems(
                $event,
                Tag::SHEET_ORGANIZATION_CATEGORY,
                'fr'
            )
            ->shouldBeCalled()
            ->willReturn(
                new NomenclaturesItemsView(
                    [
                        'category8' => 'Association',
                        'category5' => 'ETI',
                        'category10' => 'Expert',
                    ], 1
                )
            )
        ;

        $handler         = new OrganizationCategoryViewQueryHandler($taggedNomenclatureFilterGetter->reveal());
        $categoriesViews = $handler->handle($query);

        $this->assertEquals($expectedCategoriesViews, $categoriesViews);
    }
}
