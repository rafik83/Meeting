<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQueryHandler;
use Proximum\Vimeet\Application\View\Participant\CardListView;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\CanSeeSheet;
use Proximum\Vimeet\Domain\Sheet\SheetInfoGetter;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class SheetInfoGetterTest extends TestCase
{
    public function testSheetInfos(): void
    {
        $canSeeSheet = $this->prophesize(CanSeeSheet::class);
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $cardListViewQueryHandler = $this->prophesize(CardListViewQueryHandler::class);

        $event = EventFactory::createEvent();
        $user = UserFactory::create();
        $sheet = SheetFactory::create($event, $user);
        $sheetToDisplay = SheetFactory::create($event);
        $locale = 'fr';

        $nomenclatures = [
            new Nomenclature('title', 1, [], true, $event),
            new Nomenclature('title2', 1, [], true, $event),
        ];

        $cardListViewQuery = new CardListViewQuery($sheetToDisplay, $user, $locale);
        $cardListView = new CardListView();
        $cardListView->cardViews = [
            new CardView(1, false, 'toto', 'tata', 1, 'avatar', true, $sheetToDisplay->getId()),
        ];

        $templateData = new TemplateData('type', [], $locale, $locale);

        $expectedResult = [$nomenclatures, $cardListView, $templateData->getAllTaggedDatas()];

        $sheetInfosGetter = new SheetInfoGetter(
            $canSeeSheet->reveal(),
            $nomenclatureRepository->reveal(),
            $templateDataFactory->reveal(),
            $cardListViewQueryHandler->reveal()
        );

        $canSeeSheet->isSatisfiedBy($sheet, $sheetToDisplay)
            ->shouldBeCalled()
            ->willReturn(true);

        $nomenclatureRepository->findByEvent($event)
            ->shouldBeCalled()
            ->willReturn($nomenclatures);

        $cardListViewQueryHandler->handle($cardListViewQuery)
            ->shouldBeCalled()
            ->willReturn($cardListView);

        $templateDataFactory->createRegistrationFromSheet($sheetToDisplay, $locale)
            ->shouldBeCalled()
            ->willReturn($templateData);

        $result = $sheetInfosGetter->sheetInfos($event, $sheet, $sheetToDisplay, $user, $locale);

        $this->assertEquals($result, $expectedResult);
    }

    public function testAccessDenied(): void
    {
        $canSeeSheet = $this->prophesize(CanSeeSheet::class);
        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $cardListViewQueryHandler = $this->prophesize(CardListViewQueryHandler::class);

        $event = EventFactory::createEvent();
        $user = UserFactory::create();
        $sheet = SheetFactory::create($event, $user);
        $sheetToDisplay = SheetFactory::create($event);
        $locale = 'fr';

        $sheetInfosGetter = new SheetInfoGetter(
            $canSeeSheet->reveal(),
            $nomenclatureRepository->reveal(),
            $templateDataFactory->reveal(),
            $cardListViewQueryHandler->reveal()
        );

        $canSeeSheet->isSatisfiedBy($sheet, $sheetToDisplay)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        $nomenclatureRepository->findByEvent()
            ->shouldNotBeCalled();

        $cardListViewQueryHandler->handle()
            ->shouldNotBeCalled();

        $templateDataFactory->createRegistrationFromSheet()
            ->shouldNotBeCalled();

        $sheetInfosGetter->sheetInfos($event, $sheet, $sheetToDisplay, $user, $locale);
    }
}
