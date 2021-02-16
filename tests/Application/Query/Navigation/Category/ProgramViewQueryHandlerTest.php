<?php

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Category;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Navigation\Category\ProgramViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\ProgramViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\View\Happening\CategoryListView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ProgramViewQueryHandlerTest extends TestCase
{
    public function test_return_categories()
    {
        $eventFactory = new EventFactory();
        $user = new User('test@test.com', 'azerty', 'password', 'fr');
        $event = $eventFactory::createEvent();
        $type = new Type($event);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event);
        $sheet->getType()->shouldBeCalled()->willReturn($type);
        $sheet->getId()->shouldBeCalled()->willReturn(1337);

        $happeningsAccessChecker = $this->prophesize(HappeningsAccessChecker::class);
        $happeningsAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);

        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoryRepository
            ->getCategoryListViewByType($type, 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    new CategoryListView(1, 'Conférences', 'picto1', 10, '#fff', '#000'),
                    new CategoryListView(1, 'Remise des prix', 'picto2', 20, '#fff', '#000'),
                ]
            )
        ;

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $navigationBuilder
            ->getRoute('happening_program', ['sheet' => 1337])
            ->shouldBeCalled()
            ->willReturn()
        ;

        $programViewQueryHandler = new ProgramViewQueryHandler(
            $navigationBuilder->reveal(),
            $happeningsAccessChecker->reveal(),
            $categoryRepository->reveal()
        );

        $this->assertEquals(
            new CategoryView(
                'navigation.category.program',
                'icon-PresFlash_2',
                [
                    new LinkView('Conférences'),
                    new LinkView('Remise des prix'),
                ],
                true
            ),
            $programViewQueryHandler->handle(new ProgramViewQuery($sheet->reveal(), $user, 'fr'))
        );
    }

    public function test_return_null()
    {
        $eventFactory = new EventFactory();
        $sheetFactory = new SheetFactory();
        $user = new User('test@test.com', 'azerty', 'password', 'fr');
        $event = $eventFactory::createEvent();
        $type = new Type($event);
        $sheet = $sheetFactory::create($event, $user, new \DateTime(), $type);

        $happeningsAccessChecker = $this->prophesize(HappeningsAccessChecker::class);
        $happeningsAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);

        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoryRepository
            ->getCategoryListViewByType($type, 'fr')
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);

        $programViewQueryHandler = new ProgramViewQueryHandler(
            $navigationBuilder->reveal(),
            $happeningsAccessChecker->reveal(),
            $categoryRepository->reveal()
        );

        $this->assertNull($programViewQueryHandler->handle(new ProgramViewQuery($sheet, $user, 'fr')));
    }
}
