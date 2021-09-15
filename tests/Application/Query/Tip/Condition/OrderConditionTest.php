<?php

namespace Proximum\Vimeet\Tests\Application\Query\Tip\Condition;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Tip\Condition\OrderCondition;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\SheetOrderStatus;

class OrderConditionTest extends TestCase
{
    /** @var ObjectProphecy|SheetOrderStatus */
    private $sheetOrderStatus;

    /** @var OrderCondition */
    private $orderCondition;

    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|Type */
    private $type;

    /** @var ObjectProphecy|Sheet */
    private $sheet;

    /** @var ObjectProphecy|User */
    private $user;

    protected function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->user = $this->prophesize(User::class);

        $this->sheetOrderStatus = $this->prophesize(SheetOrderStatus::class);
        $this->orderCondition = new OrderCondition($this->sheetOrderStatus->reveal());
    }

    public function testNoConditionOnOrder()
    {
        $this->assertTrue(
            $this->orderCondition->isSatisfiedBy(
                new TipTranslationViewQuery(
                    $this->sheet->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_AGENDA,
                    'fr'
                ),
                new TipTranslationView(
                    123,
                    'My tip',
                    'My content',
                    'Admin title',
                    Tip::DISPLAY_DEFAULT,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null
                )
            )
        );

        $this->assertTrue(
            $this->orderCondition->isSatisfiedBy(
                new TipTranslationViewQuery(
                    $this->sheet->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_AGENDA,
                    'fr'
                ),
                new TipTranslationView(
                    123,
                    'My tip',
                    'My content',
                    'Admin title',
                    Tip::DISPLAY_DEFAULT,
                    null,
                    null,
                    null,
                    null,
                    null,
                    []
                )
            )
        );
    }

    public function testOrderStatusSuperiorZeroAndNoConditionOnSuperiorZero()
    {
        $this->sheetOrderStatus
            ->getStatus($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(Sheet\Constant::ORDER_STATUS_TOTAL_ORDER_SUPERIOR_ZERO)
        ;

        $this->assertFalse(
            $this->orderCondition->isSatisfiedBy(
                new TipTranslationViewQuery(
                    $this->sheet->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_AGENDA,
                    'fr'
                ),
                new TipTranslationView(
                    123,
                    'My tip',
                    'My content',
                    'Admin title',
                    Tip::DISPLAY_DEFAULT,
                    null,
                    null,
                    null,
                    null,
                    null,
                    [Tip::CONDITION_ON_ORDERS_WITHOUT, Tip::CONDITION_ON_ORDERS_TOTAL_EQUAL_ZERO]
                )
            )
        );
    }

    public function testOrderStatusSuperiorZeroAndConditionOnSuperiorZero()
    {
        $this->sheetOrderStatus
            ->getStatus($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(Sheet\Constant::ORDER_STATUS_TOTAL_ORDER_SUPERIOR_ZERO)
        ;

        $this->assertTrue(
            $this->orderCondition->isSatisfiedBy(
                new TipTranslationViewQuery(
                    $this->sheet->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_AGENDA,
                    'fr'
                ),
                new TipTranslationView(
                    123,
                    'My tip',
                    'My content',
                    'Admin title',
                    Tip::DISPLAY_DEFAULT,
                    null,
                    null,
                    null,
                    null,
                    null,
                    [Tip::CONDITION_ON_ORDERS_TOTAL_SUPERIOR_ZERO]
                )
            )
        );
    }

    public function testOrderStatusEqualZeroAndNoConditionOnEqualZero()
    {
        $this->sheetOrderStatus
            ->getStatus($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(Sheet\Constant::ORDER_STATUS_TOTAL_ORDER_EQUAL_ZERO)
        ;

        $this->assertFalse(
            $this->orderCondition->isSatisfiedBy(
                new TipTranslationViewQuery(
                    $this->sheet->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_AGENDA,
                    'fr'
                ),
                new TipTranslationView(
                    123,
                    'My tip',
                    'My content',
                    'Admin title',
                    Tip::DISPLAY_DEFAULT,
                    null,
                    null,
                    null,
                    null,
                    null,
                    [Tip::CONDITION_ON_ORDERS_TOTAL_SUPERIOR_ZERO, Tip::CONDITION_ON_ORDERS_WITHOUT]
                )
            )
        );
    }

    public function testOrderStatusEqualZeroAndConditionOnEqualZero()
    {
        $this->sheetOrderStatus
            ->getStatus($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(Sheet\Constant::ORDER_STATUS_TOTAL_ORDER_EQUAL_ZERO)
        ;

        $this->assertTrue(
            $this->orderCondition->isSatisfiedBy(
                new TipTranslationViewQuery(
                    $this->sheet->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_AGENDA,
                    'fr'
                ),
                new TipTranslationView(
                    123,
                    'My tip',
                    'My content',
                    'Admin title',
                    Tip::DISPLAY_DEFAULT,
                    null,
                    null,
                    null,
                    null,
                    null,
                    [Tip::CONDITION_ON_ORDERS_TOTAL_SUPERIOR_ZERO, Tip::CONDITION_ON_ORDERS_TOTAL_EQUAL_ZERO]
                )
            )
        );
    }

    public function testNoOrderStatusAndNoConditionOnWhithoutOrder()
    {
        $this->sheetOrderStatus
            ->getStatus($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(Sheet\Constant::NO_ORDER)
        ;

        $this->assertFalse(
            $this->orderCondition->isSatisfiedBy(
                new TipTranslationViewQuery(
                    $this->sheet->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_AGENDA,
                    'fr'
                ),
                new TipTranslationView(
                    123,
                    'My tip',
                    'My content',
                    'Admin title',
                    Tip::DISPLAY_DEFAULT,
                    null,
                    null,
                    null,
                    null,
                    null,
                    [Tip::CONDITION_ON_ORDERS_TOTAL_SUPERIOR_ZERO, Tip::CONDITION_ON_ORDERS_TOTAL_EQUAL_ZERO]
                )
            )
        );
    }

    public function testNoOrderStatusAndConditionOnWhithoutOrder()
    {
        $this->sheetOrderStatus
            ->getStatus($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(Sheet\Constant::NO_ORDER)
        ;

        $this->assertTrue(
            $this->orderCondition->isSatisfiedBy(
                new TipTranslationViewQuery(
                    $this->sheet->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_AGENDA,
                    'fr'
                ),
                new TipTranslationView(
                    123,
                    'My tip',
                    'My content',
                    'Admin title',
                    Tip::DISPLAY_DEFAULT,
                    null,
                    null,
                    null,
                    null,
                    null,
                    [Tip::CONDITION_ON_ORDERS_TOTAL_SUPERIOR_ZERO, Tip::CONDITION_ON_ORDERS_WITHOUT]
                )
            )
        );
    }
}
