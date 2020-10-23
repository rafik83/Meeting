<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Catalog;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetsViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetListView;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Catalog\ExportAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViews;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsResult;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\FilterAvailableSlotAndSpecificSlotCheckerView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $catalogAccessChecker;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $catalogFilterViewHandler;

    /** @var ObjectProphecy */
    private $filterAvailableSlotAndSpecificSlotCheckerHandler;

    /** @var ObjectProphecy */
    private $serializerAdapter;

    /** @var \DateTime */
    private $dateTime;

    /** @var ObjectProphecy */
    private $event;

    /** @var Request */
    private $request;

    /** @var ObjectProphecy */
    private $eventDomain;

    /** @var ObjectProphecy */
    private $userDomain;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $user;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->catalogAccessChecker = $this->prophesize(CatalogAccessChecker::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->catalogFilterViewHandler = $this->prophesize(CatalogFilterViewsHandler::class);
        $this->filterAvailableSlotAndSpecificSlotCheckerHandler = $this->prophesize(FilterAvailableSlotAndSpecificSlotCheckerHandler::class);
        $this->serializerAdapter = $this->prophesize(SerializerAdapterInterface::class);
        $this->dateTime = new \DateTime();
        $this->eventDomain = $this->prophesize(EventDomain::class);
        $this->request = new Request(['slot_id' => 1]);
        $this->request->setLocale('fr');
        $this->event = $this->prophesize(Event::class);
        $this->userDomain = $this->prophesize(UserDomain::class);
        $this->user = $this->prophesize(User::class);
        $this->sheet = $this->prophesize(Sheet::class);

        $this->userDomain->getUser()->willReturn($this->user->reveal());
        $this->eventDomain->getEvent()->willReturn($this->event->reveal());
    }

    public function testAuthenticated()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')->shouldBeCalled()->willReturn(false);

        $action = new ExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->catalogAccessChecker->reveal(),
            $this->formFactory->reveal(),
            $this->queryBus->reveal(),
            $this->catalogFilterViewHandler->reveal(),
            $this->filterAvailableSlotAndSpecificSlotCheckerHandler->reveal(),
            $this->serializerAdapter->reveal(),
            $this->dateTime
        );

        $action($this->request, $this->eventDomain->reveal(), $this->sheet->reveal(), $this->userDomain->reveal());
    }

    public function testSheetAccess()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new ExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->catalogAccessChecker->reveal(),
            $this->formFactory->reveal(),
            $this->queryBus->reveal(),
            $this->catalogFilterViewHandler->reveal(),
            $this->filterAvailableSlotAndSpecificSlotCheckerHandler->reveal(),
            $this->serializerAdapter->reveal(),
            $this->dateTime
        );

        $action($this->request, $this->eventDomain->reveal(), $this->sheet->reveal(), $this->userDomain->reveal());
    }

    public function testSheetNotInCatalog()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->sheet->isInInternalCatalog()->willReturn(false);

        $action = new ExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->catalogAccessChecker->reveal(),
            $this->formFactory->reveal(),
            $this->queryBus->reveal(),
            $this->catalogFilterViewHandler->reveal(),
            $this->filterAvailableSlotAndSpecificSlotCheckerHandler->reveal(),
            $this->serializerAdapter->reveal(),
            $this->dateTime
        );

        $action($this->request, $this->eventDomain->reveal(), $this->sheet->reveal(), $this->userDomain->reveal());
    }

    public function testCatalogNotOpen()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->sheet->isInInternalCatalog()->willReturn(true);

        $this->catalogAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(false);

        $action = new ExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->catalogAccessChecker->reveal(),
            $this->formFactory->reveal(),
            $this->queryBus->reveal(),
            $this->catalogFilterViewHandler->reveal(),
            $this->filterAvailableSlotAndSpecificSlotCheckerHandler->reveal(),
            $this->serializerAdapter->reveal(),
            $this->dateTime
        );

        $action($this->request, $this->eventDomain->reveal(), $this->sheet->reveal(), $this->userDomain->reveal());
    }

    public function testNoTypeAndCategory()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->sheet->isInInternalCatalog()->willReturn(true);
        $this->catalogAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(true);

        $catalogFilterViewResult = $this->prophesize(CatalogFilterViewsResult::class);
        $this->catalogFilterViewHandler
            ->handle(new CatalogFilterViews(
                $this->event->reveal(),
                $this->sheet->reveal(),
                'fr'
            ))
            ->shouldBeCalled()
            ->willReturn($catalogFilterViewResult->reveal())
        ;

        $catalogFilterViewResult->hasEmptyCategoryOrType()->willReturn(true);

        $action = new ExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->catalogAccessChecker->reveal(),
            $this->formFactory->reveal(),
            $this->queryBus->reveal(),
            $this->catalogFilterViewHandler->reveal(),
            $this->filterAvailableSlotAndSpecificSlotCheckerHandler->reveal(),
            $this->serializerAdapter->reveal(),
            $this->dateTime
        );

        $action($this->request, $this->eventDomain->reveal(), $this->sheet->reveal(), $this->userDomain->reveal());
    }

    public function testEmptyFilter()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->sheet->isInInternalCatalog()->willReturn(true);
        $type = $this->prophesize(Type::class);
        $type->canDisplayAnalyticsOnCatalog()->willReturn(false);
        $this->sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $this->catalogAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(true);

        $typeViews = [];
        $positionViews = [];

        $catalogFilterViewResult = new CatalogFilterViewsResult(
            CatalogFilterViewsResult::RESULT_CATEGORY_OR_TYPE,
            [],
            $typeViews,
            [],
            $positionViews,
            [],
            null
        );
        $this->catalogFilterViewHandler
            ->handle(new CatalogFilterViews(
                $this->event->reveal(),
                $this->sheet->reveal(),
                'fr'
            ))
            ->shouldBeCalled()
            ->willReturn($catalogFilterViewResult)
        ;

        $this
            ->filterAvailableSlotAndSpecificSlotCheckerHandler
            ->handle(new FilterAvailableSlotAndSpecificSlotChecker(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                1
            ))
            ->shouldBeCalled()
            ->willReturn(new FilterAvailableSlotAndSpecificSlotCheckerView(false, null))
        ;

        $form = $this->prophesize(Form::class);
        $this->formFactory->createNamed(
                '',
                SearchType::class,
                ['type' => [], 'categories' => [], 'availableSlot' => 'catalog_everyone'],
                [
                    'filterBySheetVisit' => false,
                    'typeViews' => $typeViews,
                    'categoryViews' => [],
                    'organizationCategoryViews' => [],
                    'positionViews' => $positionViews,
                    'event' => $this->event->reveal(),
                    'locale' => 'fr',
                    'filterByAvailableSlotIds' => false,
                    'filterBySpecificSlot' => false,
                    'specificSlot' => null,
                    'taggedNomenclatureTagViews' => [],
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(false);

        $view = new SheetListView([], [], [], 'participantColumn', 'typeColumn', true);
        $this->queryBus->handle(new SheetsViewQuery(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                [
                    'enabled' => true,
                    'inCatalog' => true,
                    'type' => [],
                    'categories' => [],
                    'availableSlot' => 'catalog_everyone',
                    'orderBy' => 'alphabetical',
                ],
                'fr',
                [],
                [],
                true
            ))
            ->shouldBeCalled()
            ->willReturn($view)
        ;

        $seriliazed = "type;azerty;participant;ytreza;\ntypeColumn;registration1;participantColumn;sheet1;\nExposant;AAnera;President;Needs;\n";

        $this->serializerAdapter->serialize($view, 'csv', [
                'charset' => Charset::WINDOWS_1252,
                'csv_delimiter' => ';',
            ])
            ->shouldBeCalled()
            ->willReturn($seriliazed)
        ;

        $action = new ExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->catalogAccessChecker->reveal(),
            $this->formFactory->reveal(),
            $this->queryBus->reveal(),
            $this->catalogFilterViewHandler->reveal(),
            $this->filterAvailableSlotAndSpecificSlotCheckerHandler->reveal(),
            $this->serializerAdapter->reveal(),
            $this->dateTime
        );

        $result = $action($this->request, $this->eventDomain->reveal(), $this->sheet->reveal(), $this->userDomain->reveal());

        $this->assertInstanceOf(CsvFileResponse::class, $result);
        $this->assertEquals("typeColumn;registration1;participantColumn;sheet1;\nExposant;AAnera;President;Needs;\n", $result->getContent());
    }

    public function testHandleFilter()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('IS_AUTHENTICATED_REMEMBERED')
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted(SheetVoter::EDIT, $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->sheet->isInInternalCatalog()->willReturn(true);
        $type = $this->prophesize(Type::class);
        $type->canDisplayAnalyticsOnCatalog()->willReturn(true);
        $this->sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $this->catalogAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(true);

        $typeViews = [];
        $positionViews = [];

        $catalogFilterViewResult = new CatalogFilterViewsResult(
            CatalogFilterViewsResult::RESULT_CATEGORY_OR_TYPE,
            [],
            $typeViews,
            [],
            $positionViews,
            [],
            null
        );
        $this->catalogFilterViewHandler
            ->handle(new CatalogFilterViews(
                $this->event->reveal(),
                $this->sheet->reveal(),
                'fr'
            ))
            ->shouldBeCalled()
            ->willReturn($catalogFilterViewResult)
        ;

        $this
            ->filterAvailableSlotAndSpecificSlotCheckerHandler
            ->handle(new FilterAvailableSlotAndSpecificSlotChecker(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                1
            ))
            ->shouldBeCalled()
            ->willReturn(new FilterAvailableSlotAndSpecificSlotCheckerView(false, null))
        ;

        $form = $this->prophesize(Form::class);
        $this->formFactory->createNamed(
            '',
            SearchType::class,
            ['type' => [], 'categories' => [], 'availableSlot' => 'catalog_everyone'],
            [
                'filterBySheetVisit' => true,
                'typeViews' => $typeViews,
                'categoryViews' => [],
                'organizationCategoryViews' => [],
                'positionViews' => $positionViews,
                'event' => $this->event->reveal(),
                'locale' => 'fr',
                'filterByAvailableSlotIds' => false,
                'filterBySpecificSlot' => false,
                'specificSlot' => null,
                'taggedNomenclatureTagViews' => [],
            ]
        )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(true);

        $form->getData()->willReturn([
            'type' => [1 => 1, 2 => 2],
            'position' => 'President',
            'categories' => [],
            'orderBy' => 'relevant',
        ]);

        $view = new SheetListView([], [], [], 'participantColumn', 'typeColumn', true);
        $this->queryBus->handle(new SheetsViewQuery(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            [
                'enabled' => true,
                'inCatalog' => true,
                'type' => [1 => 1, 2 => 2],
                'position' => 'President',
                'categories' => [],
                'orderBy' => 'alphabetical',
            ],
            'fr',
            [],
            [],
            true
        ))
            ->shouldBeCalled()
            ->willReturn($view)
        ;

        $seriliazed = "type;azerty;participant;ytreza;\ntypeColumn;registration1;participantColumn;sheet1;\nExposant;AAnera;President;Needs;\n";

        $this->serializerAdapter->serialize($view, 'csv', [
            'charset' => Charset::WINDOWS_1252,
            'csv_delimiter' => ';',
        ])
            ->shouldBeCalled()
            ->willReturn($seriliazed)
        ;

        $action = new ExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->catalogAccessChecker->reveal(),
            $this->formFactory->reveal(),
            $this->queryBus->reveal(),
            $this->catalogFilterViewHandler->reveal(),
            $this->filterAvailableSlotAndSpecificSlotCheckerHandler->reveal(),
            $this->serializerAdapter->reveal(),
            $this->dateTime
        );

        $result = $action($this->request, $this->eventDomain->reveal(), $this->sheet->reveal(), $this->userDomain->reveal());

        $this->assertInstanceOf(CsvFileResponse::class, $result);
        $this->assertEquals("typeColumn;registration1;participantColumn;sheet1;\nExposant;AAnera;President;Needs;\n", $result->getContent());
    }
}
