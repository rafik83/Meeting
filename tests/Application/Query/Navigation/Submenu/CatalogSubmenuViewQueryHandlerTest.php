<?php


namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Configuration;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Catalog\CanSeeOtherSheets;


class CatalogSubmenuViewQueryHandlerTest extends TestCase
{
    public function testShouldReturnEmptyArrayIfCatalogOnlineDateIsNull()
    {

        /*
            Case :
            -  catalogOnlineDate is null
        */

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1337);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $configuration = $this->prophesize(Configuration::class);
        $configuration->getCatalogOnlineDate()->shouldBeCalled()->willReturn(null);

        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $query = new CatalogSubmenuViewQuery($user->reveal(), $event->reveal(), 'fr', $sheet->reveal(), '', []);


        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $dateTime = new \DateTime('2020-03-14T03:14:15');
        $canSeeOtherSheets  = $this->prophesize(CanSeeOtherSheets::class);

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $handler = new CatalogSubmenuViewQueryHandler($navigationBuilder->reveal(), $dateTime, $canSeeOtherSheets->reveal(), $requestRepository->reveal());

        $result = $handler->handle(
            $query
        );

        $this->assertEquals($result, []);
    }

    public function testShouldReturnEmptyArrayIfIsNotInternalCatalog()
    {

        /*
            Case :
            -  catalogOnlineDate is NOT null
            - $query->sheet->isInInternalCatalog() is false
        */
        $sheet = $this->prophesize(Sheet::class);
        $sheet->isInInternalCatalog()->willReturn(false);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $configuration = $this->prophesize(Configuration::class);

        $catalogOnlineDate = new \DateTime('2100-01-14T03:14:15');

        $configuration->getCatalogOnlineDate()->shouldBeCalled()->willReturn($catalogOnlineDate);

        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $query = new CatalogSubmenuViewQuery($user->reveal(), $event->reveal(), 'fr', $sheet->reveal(), '', []);


        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $dateTime = new \DateTime('2020-03-14T03:14:15');
        $canSeeOtherSheets  = $this->prophesize(CanSeeOtherSheets::class);

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $handler = new CatalogSubmenuViewQueryHandler($navigationBuilder->reveal(), $dateTime, $canSeeOtherSheets->reveal(), $requestRepository->reveal());

        $result = $handler->handle(
            $query
        );

        $this->assertEquals($result, []);
    }
    public function testShoudlReturnNullIfCatalogOnlineDateisAfterDateTime()
    {

        /*
            Case :
            -  catalogOnlineDate is NOT null
            - $catalogOnlineDate is AFTER (>) dateTime
            - $query->sheet->isInInternalCatalog() is true
        */
        $sheet = $this->prophesize(Sheet::class);
        $sheet->isInInternalCatalog()->willReturn(true);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $configuration = $this->prophesize(Configuration::class);

        $dateTime = new \DateTime('2020-03-14T03:14:15');
        $catalogOnlineDate = new \DateTime('2100-01-14T03:14:15');


        $configuration->getCatalogOnlineDate()->shouldBeCalled()->willReturn($catalogOnlineDate);

        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $query = new CatalogSubmenuViewQuery($user->reveal(), $event->reveal(), 'fr', $sheet->reveal(), '', []);


        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);

        $canSeeOtherSheets  = $this->prophesize(CanSeeOtherSheets::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $handler = new CatalogSubmenuViewQueryHandler($navigationBuilder->reveal(), $dateTime, $canSeeOtherSheets->reveal(), $requestRepository->reveal());

        $result = $handler->handle(
            $query
        );

        $this->assertEquals($result, []);
    }

    /*
        After this :
         -  catalogOnlineDate is NOT null
            - $catalogOnlineDate is BEFORE (<=) dateTime
            - $query->sheet->isInInternalCatalog() is true
    */

    public function testShouldReturnASingleSubmenuButtonView()
    {
        $sheetId = 666;
        $sheet = $this->prophesize(Sheet::class);
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->getId()->shouldBeCalled()->willReturn($sheetId);
        $sheet->hasLinkedSheets()->shouldBeCalled()->willReturn(false);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $configuration = $this->prophesize(Configuration::class);

        $dateTime = new \DateTime('2020-03-14T03:14:15');
        $catalogOnlineDate = new \DateTime('2019-01-14T03:14:15');


        $configuration->getCatalogOnlineDate()->shouldBeCalled()->willReturn($catalogOnlineDate);

        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $query = new CatalogSubmenuViewQuery($user->reveal(), $event->reveal(), 'fr', $sheet->reveal(), '', []);


        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);


        $navigationBuilder->getRoute('event_meeting_list_request', [
            'sheet' => $sheetId
        ])->shouldBeCalled()->willReturn("dummyRoute");

        $canSeeOtherSheets  = $this->prophesize(CanSeeOtherSheets::class);
        $canSeeOtherSheets->isSatisfiedBy($sheet->reveal())->shouldBeCalled()->willReturn(false);

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $handler = new CatalogSubmenuViewQueryHandler($navigationBuilder->reveal(), $dateTime, $canSeeOtherSheets->reveal(), $requestRepository->reveal());
        $result = $handler->handle(
            $query
        );

        $expectedResult = [];
        $meetingTitle = 'navigation.category.meeting';

        $expectedResult[] = new SubmenuButtonView(
            Category::MEETING_ICON,
            $meetingTitle,
            'dummyRoute',
            false,
            null,
            false
        );

        $this->assertEquals($result, $expectedResult);
    }


    public function testShouldReturnASingleSubmenuButtonViewWithCounter()
    {
        $sheetId = 666;
        $sheet = $this->prophesize(Sheet::class);
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->getId()->shouldBeCalled()->willReturn($sheetId);
        $sheet->hasLinkedSheets()->shouldBeCalled()->willReturn(false);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $configuration = $this->prophesize(Configuration::class);

        $dateTime = new \DateTime('2020-03-14T03:14:15');
        $catalogOnlineDate = new \DateTime('2019-01-14T03:14:15');


        $configuration->getCatalogOnlineDate()->shouldBeCalled()->willReturn($catalogOnlineDate);

        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $query = new CatalogSubmenuViewQuery($user->reveal(), $event->reveal(), 'fr', $sheet->reveal(), '', []);


        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);


        $navigationBuilder->getRoute('event_meeting_list_request', [
            'sheet' => $sheetId
        ])->shouldBeCalled()->willReturn("dummyRoute");

        $canSeeOtherSheets  = $this->prophesize(CanSeeOtherSheets::class);
        $canSeeOtherSheets->isSatisfiedBy($sheet->reveal())->shouldBeCalled()->willReturn(false);

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->countPendingPropositionReceivedBySheet($sheet->reveal())->shouldBeCalled()->willReturn(666);

        $handler = new CatalogSubmenuViewQueryHandler($navigationBuilder->reveal(), $dateTime, $canSeeOtherSheets->reveal(), $requestRepository->reveal());

        $result = $handler->handle(
            $query
        );

        $expectedResult = [];
        $meetingTitle = 'navigation.category.meeting';

        $expectedResult[] = new SubmenuButtonView(
            Category::MEETING_ICON,
            $meetingTitle,
            'dummyRoute',
            false,
            666,
            false
        );

        $this->assertEquals($result, $expectedResult);
    }

    public function testShouldReturnANewMeetingTitle()
    {
        $sheetId = 666;
        $sheet = $this->prophesize(Sheet::class);
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->getId()->shouldBeCalled()->willReturn($sheetId);
        $sheet->hasLinkedSheets()->shouldBeCalled()->willReturn(false);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $configuration = $this->prophesize(Configuration::class);

        $dateTime = new \DateTime('2020-03-14T03:14:15');
        $catalogOnlineDate = new \DateTime('2019-01-14T03:14:15');


        $configuration->getCatalogOnlineDate()->shouldBeCalled()->willReturn($catalogOnlineDate);

        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $meetingTitle = "WAZAAAAAAAAAAAAA";

        $staticFormulation = $this->prophesize(StaticFormulation::class);
        $locale = "fr";

        $staticFormulation->getTitle($locale)->shouldBeCalled()->willReturn($meetingTitle);

        $staticFormulationsIndexedByCategory = [Category::MEETING => $staticFormulation->reveal()];


        $query = new CatalogSubmenuViewQuery($user->reveal(), $event->reveal(), $locale, $sheet->reveal(), '', $staticFormulationsIndexedByCategory);

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);


        $navigationBuilder->getRoute('event_meeting_list_request', [
            'sheet' => $sheetId
        ])->shouldBeCalled()->willReturn("dummyRoute");

        $canSeeOtherSheets  = $this->prophesize(CanSeeOtherSheets::class);
        $canSeeOtherSheets->isSatisfiedBy($sheet->reveal())->shouldBeCalled()->willReturn(false);

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->countPendingPropositionReceivedBySheet($sheet->reveal())->shouldBeCalled()->willReturn(null);

        $handler = new CatalogSubmenuViewQueryHandler($navigationBuilder->reveal(), $dateTime, $canSeeOtherSheets->reveal(), $requestRepository->reveal());

        $result = $handler->handle(
            $query
        );

        $expectedResult = [];


        $expectedResult[] = new SubmenuButtonView(
            Category::MEETING_ICON,
            $meetingTitle,
            'dummyRoute',
            false,
            null,
            false
        );

        $this->assertEquals($result, $expectedResult);
    }

    public function testShouldReturnTwoSubMenuButtonView()
    {
        $sheetId = 666;
        $sheet = $this->prophesize(Sheet::class);
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->getId()->shouldBeCalled()->willReturn($sheetId);
        $sheet->hasLinkedSheets()->shouldBeCalled()->willReturn(false);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $configuration = $this->prophesize(Configuration::class);

        $dateTime = new \DateTime('2020-03-14T03:14:15');
        $catalogOnlineDate = new \DateTime('2019-01-14T03:14:15');


        $configuration->getCatalogOnlineDate()->shouldBeCalled()->willReturn($catalogOnlineDate);

        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $meetingTitle = "WAZAAAAAAAAAAAAA";

        $staticFormulation = $this->prophesize(StaticFormulation::class);
        $locale = "fr";

        $staticFormulation->getTitle($locale)->shouldBeCalled()->willReturn($meetingTitle);

        $staticFormulationsIndexedByCategory = [Category::MEETING => $staticFormulation->reveal()];


        $query = new CatalogSubmenuViewQuery($user->reveal(), $event->reveal(), $locale, $sheet->reveal(), '', $staticFormulationsIndexedByCategory);

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);

        $canSeeOtherSheets  = $this->prophesize(CanSeeOtherSheets::class);
        $canSeeOtherSheets->isSatisfiedBy($sheet->reveal())->shouldBeCalled()->willReturn(true);

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->countPendingPropositionReceivedBySheet($sheet->reveal())->shouldBeCalled()->willReturn(null);

        $navigationBuilder->getRoute('event_meeting_list_request', [
            'sheet' => $sheetId
        ])->shouldBeCalled()->willReturn("dummyRoute");


        $navigationBuilder->getRoute('event_catalog_index', [
            'sheet' => $sheetId
        ])->shouldBeCalled()->willReturn("otherDummyRoute");


        $handler = new CatalogSubmenuViewQueryHandler($navigationBuilder->reveal(), $dateTime, $canSeeOtherSheets->reveal(), $requestRepository->reveal());

        $result = $handler->handle(
            $query
        );

        $expectedResult = [];

        $catalogTitle = 'navigation.category.catalog';
        $expectedResult[] = new SubmenuButtonView(
            Category::CATALOG_ICON,
            $catalogTitle,
            'otherDummyRoute',
            false,
            null,
            true
        );


        $expectedResult[] = new SubmenuButtonView(
            Category::MEETING_ICON,
            $meetingTitle,
            'dummyRoute',
            false,
            null,
            false
        );

        $this->assertEquals($result, $expectedResult);
    }
}
