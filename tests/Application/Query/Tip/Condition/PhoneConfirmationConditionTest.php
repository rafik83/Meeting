<?php

namespace Proximum\Vimeet\Tests\Application\Query\Tip\Condition;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Tip\Condition\PhoneConfirmationCondition;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class PhoneConfirmationConditionTest extends TestCase
{
    /** @var PhoneConfirmationCondition */
    private $phoneConfirmation;

    /** @var ObjectProphecy|UserEventPhoneChecker */
    private $userEventPhoneChecker;

    /** @var ObjectProphecy|Sheet */
    private $sheet;

    /** @var ObjectProphecy|User */
    private $user;

    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|Type */
    private $type;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->user = $this->prophesize(User::class);

        $this->userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);
        $this->phoneConfirmation = new PhoneConfirmationCondition($this->userEventPhoneChecker->reveal());
    }

    public function testNoConditionOnPhone()
    {
        $this->assertTrue(
            $this->phoneConfirmation->isSatisfiedBy(
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
                    null
                )
            )
        );
    }

    public function testPhoneMustBeConfirmedAndUserHasNotValidPhone()
    {
        $this->userEventPhoneChecker
            ->isValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->assertFalse(
            $this->phoneConfirmation->isSatisfiedBy(
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
                    true
                )
            )
        );
    }

    public function testPhoneMustBeConfirmedAndUserHasValidPhone()
    {
        $this->userEventPhoneChecker
            ->isValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->assertTrue(
            $this->phoneConfirmation->isSatisfiedBy(
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
                    true
                )
            )
        );
    }

    public function testPhoneMustNotBeConfirmedAndUserHasNotValidPhone()
    {
        $this->userEventPhoneChecker
            ->isValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->assertTrue(
            $this->phoneConfirmation->isSatisfiedBy(
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
                    false
                )
            )
        );
    }

    public function testPhoneMustNotBeConfirmedAndUserHasValidPhone()
    {
        $this->userEventPhoneChecker
            ->isValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->assertFalse(
            $this->phoneConfirmation->isSatisfiedBy(
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
                    false
                )
            )
        );
    }
}
