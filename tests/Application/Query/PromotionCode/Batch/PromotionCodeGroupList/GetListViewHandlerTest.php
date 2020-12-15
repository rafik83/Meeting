<?php

namespace Proximum\Vimeet\Tests\Application\Query\PromotionCode\Batch\PromotionCodeGroupList;

use \DateTime;
use Proximum\Vimeet\Application\Query\PromotionCode\Batch\CanBeUpdatable;
use Proximum\Vimeet\Application\Query\PromotionCode\Batch\PromotionCodeGroupList\GetListView;
use Proximum\Vimeet\Application\Query\PromotionCode\Batch\PromotionCodeGroupList\GetListViewHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\PromotionCode\Batch\PromotionCodeGroupList\PromotionCodeGroupListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Domain\Repository\PromotionCodeGroupRepositoryInterface;
use Proximum\Vimeet\Tests\Helper\EntityId;

class GetListViewHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $promotionCodeGroup1 = new PromotionCodeGroup(
            $event->reveal(),
            'My first group',
            2,
            null,
            3,
            null,
            new DateTime('2018-01-01')
        );
        EntityId::setId($promotionCodeGroup1, 111);

        $promotionCodeGroup2 = new PromotionCodeGroup(
            $event->reveal(),
            'My second group',
            10,
            'PREFIX-',
            null,
            new DateTime('2020-01-01 0:0:0.000'),
            new DateTime('2018-01-01')
        );
        EntityId::setId($promotionCodeGroup2, 222);

        $canBeUpdatable = $this->prophesize(CanBeUpdatable::class);
        $canBeUpdatable->isSatisfiableBy($promotionCodeGroup1)->shouldBeCalled()->willReturn(true);
        $canBeUpdatable->isSatisfiableBy($promotionCodeGroup2)->shouldBeCalled()->willReturn(false);

        $promotionCodeGroupRepository = $this->prophesize(PromotionCodeGroupRepositoryInterface::class);
        $promotionCodeGroupRepository
            ->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$promotionCodeGroup1, $promotionCodeGroup2]);

        $getListViewHandler = new GetListViewHandler(
            $canBeUpdatable->reveal(),
            $promotionCodeGroupRepository->reveal()
        );
        $this->assertEquals(
            [
                new PromotionCodeGroupListView(
                    111,
                    'My first group',
                    2,
                    null,
                    3,
                    null,
                    true
                ),
                new PromotionCodeGroupListView(
                    222,
                    'My second group',
                    10,
                    'PREFIX-',
                    null,
                    new DateTime('2020-01-01 0:0:0.000'),
                    false
                ),
            ],
            $getListViewHandler->handle(new GetListView($event->reveal()))
        );
    }
}
