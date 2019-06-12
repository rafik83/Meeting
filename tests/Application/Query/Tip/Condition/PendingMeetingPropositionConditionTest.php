<?php

namespace Proximum\Vimeet\Tests\Application\Query\Tip\Condition;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Tip\Condition\PendingMeetingPropositionCondition;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class PendingMeetingPropositionConditionTest extends TestCase
{
    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|Type */
    private $type;

    /** @var ObjectProphecy|Sheet */
    private $sheet;

    /** @var ObjectProphecy|User */
    private $user;

    /** @var ObjectProphecy|RequestRepositoryInterface */
    private $requestRepository;

    /** @var PendingMeetingPropositionCondition */
    private $pendingMeetingPropositionCondition;

    protected function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->user = $this->prophesize(User::class);

        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->pendingMeetingPropositionCondition = new PendingMeetingPropositionCondition(
            $this->requestRepository->reveal()
        );
    }

    public function testNoConditionOnPendingMeetingProposition()
    {
        $this->assertTrue(
            $this->pendingMeetingPropositionCondition->isSatisfiedBy(
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
                    null
                )
            )
        );
    }

    public function testConditionMustHavePendingMeetingPropositionAndSheetHasNot()
    {
        $this->requestRepository
            ->hasPendingPropositionReceivedBySheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->assertFalse(
            $this->pendingMeetingPropositionCondition->isSatisfiedBy(
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
                    true
                )
            )
        );
    }

    public function testConditionMustHavePendingMeetingPropositionAndSheetHas()
    {
        $this->requestRepository
            ->hasPendingPropositionReceivedBySheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->assertTrue(
            $this->pendingMeetingPropositionCondition->isSatisfiedBy(
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
                    true
                )
            )
        );
    }

    public function testConditionMustNotHavePendingMeetingPropositionAndSheetHas()
    {
        $this->requestRepository
            ->hasPendingPropositionReceivedBySheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->assertFalse(
            $this->pendingMeetingPropositionCondition->isSatisfiedBy(
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
                    false
                )
            )
        );
    }

    public function testConditionMustNotHavePendingMeetingPropositionAndSheetNotHave()
    {
        $this->requestRepository
            ->hasPendingPropositionReceivedBySheet($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->assertTrue(
            $this->pendingMeetingPropositionCondition->isSatisfiedBy(
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
                    false
                )
            )
        );
    }
}
