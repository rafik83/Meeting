<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Catalog;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Catalog\NomenclatureTagViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\OrganizationCategoryViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\PositionViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SearchFacet\SearchFacetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Domain\Catalog\GetDisplayObjectiveFilter;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationCategories;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViews;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsResult;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class CatalogFilterViewsHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    public $queryBus;

    /** @var ObjectProphecy */
    public $visibleParticipationCategories;

    /** @var ObjectProphecy */
    public $visibleParticipationTypes;

    /** @var ObjectProphecy */
    public $engine;

    /** @var ObjectProphecy */
    public $event;

    /** @var ObjectProphecy */
    public $sheet;

    /** @var string */
    public $locale;

    /** @var ObjectProphecy */
    public $canDisplayObjectiveFilter;

    public function setUp()
    {
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->visibleParticipationCategories = $this->prophesize(VisibleParticipationCategories::class);
        $this->visibleParticipationTypes = $this->prophesize(VisibleParticipationTypes::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->locale = 'fr';
        $this->canDisplayObjectiveFilter = $this->prophesize(GetDisplayObjectiveFilter::class);
    }

    public function testHandleNoType()
    {
        $view = new CatalogFilterViews(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->locale
        );

        $this->canDisplayObjectiveFilter->__invoke($this->sheet->reveal(), $this->locale)->shouldNotBeCalled();

        $searchFacetView = new SearchFacetView('type', 'label', 'placeholder', true);
        $searchFacetsView = new SearchFacetsView([$searchFacetView]);
        $query = new SearchFacetViewQuery($this->event->reveal(), 'fr');
        $this->queryBus->handle($query)->shouldBeCalled()->willReturn($searchFacetsView);

        $this->visibleParticipationTypes->getAllowedTypesList($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $response = new Response();

        $this->engine->renderResponse(
                'EventBundle:Catalog:no-visible-type.html.twig',
                ['event' => $this->event->reveal(), 'sheet' => $this->sheet->reveal()]
            )
            ->shouldBeCalled()
            ->willReturn($response);

        $handler = new CatalogFilterViewsHandler(
            $this->queryBus->reveal(),
            $this->visibleParticipationCategories->reveal(),
            $this->visibleParticipationTypes->reveal(),
            $this->engine->reveal(),
            $this->canDisplayObjectiveFilter->reveal()
        );

        $result = $handler->handle($view);

        $expected = new CatalogFilterViewsResult(
            CatalogFilterViewsResult::EMPTY_CATEGORY_OR_TYPE,
            [],
            [],
            [],
            [],
            [],
            [],
            $response
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        $view = new CatalogFilterViews(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->locale
        );

        $this->canDisplayObjectiveFilter->__invoke($this->sheet->reveal(), $this->locale)->shouldBeCalled()->willReturn(['need']);

        $searchFacetView1 = new SearchFacetView('type', 'label', 'placeholder', true);
        $searchFacetView2 = new SearchFacetView('position', 'label', 'placeholder', true);
        $searchFacetsView = new SearchFacetsView([$searchFacetView1, $searchFacetView2], ['sheet_organization_category' => []]);
        $query = new SearchFacetViewQuery($this->event->reveal(), 'fr');
        $this->queryBus->handle($query)->shouldBeCalled()->willReturn($searchFacetsView);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $this->visibleParticipationTypes->getAllowedTypesList($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$type1->reveal(), $type2->reveal()])
        ;

        $typeView1 = $this->prophesize(TypeView::class);
        $typeView2 = $this->prophesize(TypeView::class);
        $this->queryBus
            ->handle(new TypeViewQuery($this->event->reveal(), [$type1->reveal(), $type2->reveal()], 'fr'))
            ->shouldBeCalled()
            ->willReturn([$typeView1->reveal(), $typeView2->reveal()])
        ;
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();

        $positionView1 = $this->prophesize(PositionView::class);
        $positionView2 = $this->prophesize(PositionView::class);
        $positionViews = [
            $positionView1->reveal(),
            $positionView2->reveal(),
        ];
        $this->queryBus
             ->handle(new OrganizationCategoryViewQuery($this->event->reveal(), 'fr'))
             ->shouldNotBeCalled()
         ;
        $this->queryBus
             ->handle(new PositionViewQuery($this->event->reveal(), 'fr'))
             ->shouldBeCalled()
             ->willReturn($positionViews)
         ;

        $this->queryBus
            ->handle(new NomenclatureTagViewQuery($this->event->reveal(), [0 => 'sheet_organization_category'], 'fr'))
            ->shouldBeCalled()
            ->willReturn([
                'sheet_organization_category' => [],
            ])
        ;

        $handler = new CatalogFilterViewsHandler(
            $this->queryBus->reveal(),
            $this->visibleParticipationCategories->reveal(),
            $this->visibleParticipationTypes->reveal(),
            $this->engine->reveal(),
            $this->canDisplayObjectiveFilter->reveal()
        );

        $result = $handler->handle($view);

        $expected = new CatalogFilterViewsResult(
            CatalogFilterViewsResult::RESULT_CATEGORY_OR_TYPE,
            [],
            [],
            [$typeView1->reveal(), $typeView2->reveal()],
            [],
            $positionViews,
            [
                'sheet_organization_category' => [],
            ],
            null,
            ['need']
        );

        $this->assertEquals($expected, $result);
    }
}
