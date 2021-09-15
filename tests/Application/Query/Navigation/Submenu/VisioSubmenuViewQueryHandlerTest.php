<?php


namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\VisioSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\VisioSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Application\View\Navigation\SubmenuView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Navigation\NavigationBuilder;

class VisioSubmenuViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $navigationBuilder;

    /** @var \DateTimeInterface */
    private $currentDate;

    public function setUp(): void
    {
        $this->navigationBuilder = $this->prophesize(NavigationBuilder::class);
        $this->currentDate = new \DateTime('2020-05-13 10:00:00.000');
    }

    public function testHandleNotEnable(): void
    {
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $event->getConfiguration()
            ->shouldBeCalled()
            ->willReturn($configuration->reveal())
        ;
        $configuration
            ->getEnableVisioTestMenuButtonDate()
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->navigationBuilder
            ->getRoute(Route::VISIO_TEST_SHEET_CREATE_TEST, Argument::any())
            ->shouldNotBeCalled()
        ;

        $query = new VisioSubmenuViewQuery($event->reveal(), $sheet->reveal(), 'fr', 'event_sheet_default', null);
        $handler = new VisioSubmenuViewQueryHandler(
            $this->navigationBuilder->reveal(),
            $this->currentDate
        );

        $result = $handler->handle($query);

        $this->assertNull($result);
    }

    public function testHandleInTheFuture(): void
    {
        $date = new \DateTime('2020-05-20 10:00:00.000');
        $sheet = $this->prophesize(Sheet::class);
        $event = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $event->getConfiguration()
            ->shouldBeCalled()
            ->willReturn($configuration->reveal())
        ;
        $configuration
            ->getEnableVisioTestMenuButtonDate()
            ->shouldBeCalled()
            ->willReturn($date)
        ;

        $this->navigationBuilder
            ->getRoute(Route::VISIO_TEST_SHEET_CREATE_TEST, Argument::any())
            ->shouldNotBeCalled()
        ;

        $query = new VisioSubmenuViewQuery($event->reveal(), $sheet->reveal(), 'fr', 'event_sheet_default', null);
        $handler = new VisioSubmenuViewQueryHandler(
            $this->navigationBuilder->reveal(),
            $this->currentDate
        );

        $result = $handler->handle($query);

        $this->assertNull($result);
    }

    public function testHandle(): void
    {
        $date = new \DateTime('2020-05-10 10:00:00.000');
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->shouldBeCalled()->willReturn(12);
        $event = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $event->getConfiguration()
            ->shouldBeCalled()
            ->willReturn($configuration->reveal())
        ;
        $configuration
            ->getEnableVisioTestMenuButtonDate()
            ->shouldBeCalled()
            ->willReturn($date)
        ;

        $this->navigationBuilder->getRoute(Route::VISIO_TEST_SHEET_CREATE_TEST, ['sheet' => 12])
            ->shouldBeCalled()
            ->willReturn('/sheet/12/video/network/test')
        ;

        $query = new VisioSubmenuViewQuery($event->reveal(), $sheet->reveal(), 'fr', 'event_sheet_default', null);
        $handler = new VisioSubmenuViewQueryHandler(
            $this->navigationBuilder->reveal(),
            $this->currentDate
        );

        $result = $handler->handle($query);

        $expected = new SubmenuButtonView(
            Category::VISIO_ICON,
            Category::VISIO,
            '/sheet/12/video/network/test',
            false,
            null,
            true
        );

        $this->assertEquals($expected, $result);
    }
}
