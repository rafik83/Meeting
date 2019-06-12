<?php

namespace Proximum\Vimeet\Tests\Application\Query\Tip\Condition;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Tip\Condition\RemainingToPayCondition;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Balance;

class RemainingToPayConditionTest extends TestCase
{
    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|Type */
    private $type;

    /** @var ObjectProphecy|Sheet */
    private $sheet;

    /** @var ObjectProphecy|User */
    private $user;

    /** @var ObjectProphecy|Balance */
    private $balance;

    /** @var RemainingToPayCondition */
    private $remainingToPayCondition;

    protected function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->user = $this->prophesize(User::class);

        $this->balance = $this->prophesize(Balance::class);
        $this->remainingToPayCondition = new RemainingToPayCondition($this->balance->reveal());
    }

    public function testNoConditionOnRemainingToPay()
    {
        $this->assertTrue(
            $this->remainingToPayCondition->isSatisfiedBy(
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
                    null
                )
            )
        );
    }

    public function testMustNotHaveRemainingToPayAndSheetHasNotRemainingToPay()
    {
        $this->balance->getRemainingToPay($this->sheet->reveal())->willReturn(0);

        $this->assertTrue(
            $this->remainingToPayCondition->isSatisfiedBy(
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
                    false
                )
            )
        );
    }

    public function testMustHaveRemainingToPayAndSheetHasRemainingToPay()
    {
        $this->balance->getRemainingToPay($this->sheet->reveal())->willReturn(99);

        $this->assertTrue(
            $this->remainingToPayCondition->isSatisfiedBy(
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
                    true
                )
            )
        );
    }

    public function testMustNotHaveRemainingToPayAndSheetHasRemainingToPay()
    {
        $this->balance->getRemainingToPay($this->sheet->reveal())->willReturn(99);

        $this->assertFalse(
            $this->remainingToPayCondition->isSatisfiedBy(
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
                    false
                )
            )
        );
    }

    public function testMustHaveRemainingToPayAndSheetHasNotRemainingToPay()
    {
        $this->balance->getRemainingToPay($this->sheet->reveal())->willReturn(0);

        $this->assertFalse(
            $this->remainingToPayCondition->isSatisfiedBy(
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
                    true
                )
            )
        );
    }
}
