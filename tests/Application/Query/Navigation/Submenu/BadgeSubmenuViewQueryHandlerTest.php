<?php

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\BadgeSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\BadgeSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Badge\AvailableChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class BadgeSubmenuViewQueryHandlerTest extends TestCase
{
    public function testBadgeAvailable()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1337);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $event = $this->prophesize(Event::class);

        $availableChecker = $this->prophesize(AvailableChecker::class);
        $availableChecker->isSatisfiedBy($sheet->reveal())->shouldBeCalled()->willReturn(true);

        $router = $this->prophesize(RouterInterface::class);
        $router
            ->generate(
                'event_sheet_user_badge',
                [
                    'sheet' => 1337,
                    'user' => 42,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('/url/to/badge')
        ;

        $badgeSubmenuViewQueryHandler = new BadgeSubmenuViewQueryHandler(
            $availableChecker->reveal(),
            $router->reveal()
        );
        $result = $badgeSubmenuViewQueryHandler->handle(
            new BadgeSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal(),
                'event_sheet_user_badge'
            )
        );

        $expectedSubmenuButtonView = new SubmenuButtonView(
            Category::BADGE_ICON,
            Category::BADGE,
            '/url/to/badge',
            true,
            null,
            true
        );

        $this->assertEquals($expectedSubmenuButtonView, $result);
    }

    public function testBadgeNotAvailable()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1337);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);

        $event = $this->prophesize(Event::class);

        $availableChecker = $this->prophesize(AvailableChecker::class);
        $availableChecker->isSatisfiedBy($sheet->reveal())->shouldBeCalled()->willReturn(false);

        $router = $this->prophesize(RouterInterface::class);

        $badgeSubmenuViewQueryHandler = new BadgeSubmenuViewQueryHandler(
            $availableChecker->reveal(),
            $router->reveal()
        );

        $this->assertNull($badgeSubmenuViewQueryHandler->handle(
            new BadgeSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal(),
                'event_sheet_user_badge'
            )
        ));
    }
}
