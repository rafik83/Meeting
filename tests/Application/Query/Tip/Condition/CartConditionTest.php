<?php

namespace Proximum\Vimeet\Tests\Application\Query\Tip\Condition;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Tip\Condition\CartCondition;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class CartConditionTest extends TestCase
{
    /** @var ObjectProphecy|Sheet */
    private $sheet;

    /** @var ObjectProphecy|User */
    private $user;

    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|Type */
    private $type;

    /** @var ObjectProphecy|CartRowRepositoryInterface */
    private $cartRowRepository;

    /** @var CartCondition */
    private $cartCondition;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->user = $this->prophesize(User::class);

        $this->cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $this->cartCondition = new CartCondition($this->cartRowRepository->reveal());
    }

    public function testNoConditionOnCart()
    {
        $this->assertTrue(
            $this->cartCondition->isSatisfiedBy(
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
                    null
                )
            )
        );
    }

    public function testMustHasCartAndUserHasNotCart()
    {
        $this->cartRowRepository
            ->findBySheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->assertFalse(
            $this->cartCondition->isSatisfiedBy(
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
                    true
                )
            )
        );
    }

    public function testMustHasCartAndUserHasCart()
    {
        $this->cartRowRepository
            ->findBySheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->assertTrue(
            $this->cartCondition->isSatisfiedBy(
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
                    true
                )
            )
        );
    }

    public function testMustNotHasCartAndUserHasNotCart()
    {
        $this->cartRowRepository
            ->findBySheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->assertTrue(
            $this->cartCondition->isSatisfiedBy(
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
                    false
                )
            )
        );
    }

    public function testMustNotHasCartAndUserHasCart()
    {
        $this->cartRowRepository
            ->findBySheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->assertFalse(
            $this->cartCondition->isSatisfiedBy(
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
                    false
                )
            )
        );
    }
}
