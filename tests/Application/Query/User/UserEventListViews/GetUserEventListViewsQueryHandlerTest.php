<?php

namespace Proximum\Vimeet\Tests\Application\Query\User\UserEventListViews;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView\GetUserEventListViewsByEventInterface;
use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserEventListViewsQuery;
use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserEventListViewsQueryHandler;
use Proximum\Vimeet\Application\Query\User\UserEventListViews\UserEventListViews;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;

class GetUserEventListViewsQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $paginatedResult = $this->prophesize(PaginatedResult::class);
        $condition = $this->prophesize(Condition::class);

        $getUserEventViewsByEvent = $this->prophesize(GetUserEventListViewsByEventInterface::class);
        $getUserEventViewsByEvent
            ->handle($event, 1, 'fr', $condition->reveal())
            ->shouldBeCalled()
            ->willReturn($paginatedResult->reveal())
        ;

        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoryRepository
            ->eventHasCategories($event)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $getUserEventListViewsQueryHandler = new GetUserEventListViewsQueryHandler(
            $getUserEventViewsByEvent->reveal(),
            $categoryRepository->reveal()
        );

        $this->assertEquals(
            new UserEventListViews($paginatedResult->reveal(), true),
            $getUserEventListViewsQueryHandler->handle(new GetUserEventListViewsQuery($event->reveal(), 1, 'fr', $condition->reveal()))
        );
    }
}
