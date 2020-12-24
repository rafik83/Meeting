<?php

namespace Proximum\Vimeet\Tests\Application\Query\Badge;

use Proximum\Vimeet\Application\Query\Badge\GetBadgeConfigurationByTypeQuery;
use Proximum\Vimeet\Application\Query\Badge\GetBadgeConfigurationByTypeQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;

class GetBadgeConfigurationByTypeQueryHandlerTest extends TestCase
{
    public function testGetDefaultBadge()
    {
        $event = $this->prophesize(Event::class);

        $type = $this->prophesize(Type::class);
        $type->getEvent()->willReturn($event->reveal());

        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $badgeRepository->findByType($type)->shouldBeCalled()->willReturn(null);

        $handler = new GetBadgeConfigurationByTypeQueryHandler($badgeRepository->reveal());
        $result = $handler->handle(new GetBadgeConfigurationByTypeQuery($type->reveal()));
        $this->assertEquals($result, Badge::createDefault($event->reveal(), $type->reveal()));
    }

    public function testGetCustomBadge()
    {
        $badge = $this->prophesize(Badge::class);
        $type = $this->prophesize(Type::class);

        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $badgeRepository->findByType($type)->shouldBeCalled()->willReturn($badge->reveal());

        $handler = new GetBadgeConfigurationByTypeQueryHandler($badgeRepository->reveal());
        $result = $handler->handle(new GetBadgeConfigurationByTypeQuery($type->reveal()));
        $this->assertEquals($result, $badge->reveal());
    }
}
