<?php

namespace Proximum\Vimeet\Tests\Application\Query\Tip;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Tip\IsTipOpened;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipOpened;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Tip\TipOpenedRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use PHPUnit\Framework\TestCase;

class IsTipOpenedTest extends TestCase
{
    /** @var ObjectProphecy|TipRepositoryInterface */
    private $tipRepository;

    /** @var ObjectProphecy|TipOpenedRepositoryInterface */
    private $tipOpenedRepository;

    /** @var \DateTime */
    private $dateTime;

    /** @var IsTipOpened */
    private $isTipOpened;

    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|Sheet */
    private $sheet;

    /** @var ObjectProphecy|User */
    private $user;

    /** @var ObjectProphecy|Type */
    private $type;

    protected function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->user = $this->prophesize(User::class);

        $this->tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $this->tipOpenedRepository = $this->prophesize(TipOpenedRepositoryInterface::class);
        $this->dateTime = new \DateTime('2019-08-27 12:30:00');
        $this->isTipOpened = new IsTipOpened(
            $this->tipRepository->reveal(),
            $this->tipOpenedRepository->reveal(),
            $this->dateTime
        );
    }

    public function test_tip_display_default()
    {
        $this->assertFalse(
            $this->isTipOpened->isSatisfiedBy(
                new TipTranslationViewQuery($this->sheet->reveal(), $this->user->reveal(), 'my-context', 'fr'),
                new TipTranslationView(42, 'Info', 'Some content', 'My info', Tip::DISPLAY_DEFAULT)
            )
        );
    }

    public function test_tip_display_first_time_and_not_already_opened()
    {
        $tip = $this->prophesize(Tip::class);
        $this->tipRepository->getById(42)->shouldBeCalled()->willReturn($tip->reveal());

        $this->tipOpenedRepository
            ->isOpened($tip->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->tipOpenedRepository
            ->add(new TipOpened($this->user->reveal(), $tip->reveal(), $this->dateTime))
            ->shouldBeCalled()
        ;

        $this->assertTrue(
            $this->isTipOpened->isSatisfiedBy(
                new TipTranslationViewQuery($this->sheet->reveal(), $this->user->reveal(), 'my-context', 'fr'),
                new TipTranslationView(42, 'Info', 'Some content', 'My info', Tip::DISPLAY_FIRST_TIME_OPENED)
            )
        );
    }

    public function test_tip_display_first_time_and_already_opened()
    {
        $tip = $this->prophesize(Tip::class);
        $this->tipRepository->getById(42)->shouldBeCalled()->willReturn($tip->reveal());

        $this->tipOpenedRepository
            ->isOpened($tip->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->tipOpenedRepository
            ->add(new TipOpened($this->user->reveal(), $tip->reveal(), $this->dateTime))
            ->shouldNotBeCalled()
        ;

        $this->assertFalse(
            $this->isTipOpened->isSatisfiedBy(
                new TipTranslationViewQuery($this->sheet->reveal(), $this->user->reveal(), 'my-context', 'fr'),
                new TipTranslationView(42, 'Info', 'Some content', 'My info', Tip::DISPLAY_FIRST_TIME_OPENED)
            )
        );
    }

    public function test_tip_display_always_and_not_already_opened()
    {
        $tip = $this->prophesize(Tip::class);
        $this->tipRepository->getById(42)->shouldBeCalled()->willReturn($tip->reveal());

        $this->tipOpenedRepository
            ->getByTipAndUser($tip->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $this->tipOpenedRepository
            ->add(new TipOpened($this->user->reveal(), $tip->reveal(), $this->dateTime))
            ->shouldBeCalled()
        ;

        $this->assertTrue(
            $this->isTipOpened->isSatisfiedBy(
                new TipTranslationViewQuery($this->sheet->reveal(), $this->user->reveal(), 'my-context', 'fr'),
                new TipTranslationView(42, 'Info', 'Some content', 'My info', Tip::DISPLAY_ALWAYS_OPENED)
            )
        );
    }

    public function test_tip_display_always_and_already_opened_for_less_than_two_hours()
    {
        $tip = $this->prophesize(Tip::class);
        $this->tipRepository->getById(42)->shouldBeCalled()->willReturn($tip->reveal());

        $tipOpened = new TipOpened($this->user->reveal(), $tip->reveal(), new \DateTime('2019-08-27 11:00:00'));

        $this->tipOpenedRepository
            ->getByTipAndUser($tip->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($tipOpened)
        ;
        $this->tipOpenedRepository
            ->add(new TipOpened($this->user->reveal(), $tip->reveal(), $this->dateTime))
            ->shouldNotBeCalled()
        ;

        $this->assertFalse(
            $this->isTipOpened->isSatisfiedBy(
                new TipTranslationViewQuery($this->sheet->reveal(), $this->user->reveal(), 'my-context', 'fr'),
                new TipTranslationView(42, 'Info', 'Some content', 'My info', Tip::DISPLAY_ALWAYS_OPENED)
            )
        );
    }

    public function test_tip_display_always_and_already_opened_for_more_than_two_hours()
    {
        $tip = $this->prophesize(Tip::class);
        $this->tipRepository->getById(42)->shouldBeCalled()->willReturn($tip->reveal());

        $tipOpened = new TipOpened($this->user->reveal(), $tip->reveal(), new \DateTime('2019-08-27 10:00:00'));

        $this->tipOpenedRepository
            ->getByTipAndUser($tip->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($tipOpened)
        ;
        $this->tipOpenedRepository
            ->add(new TipOpened($this->user->reveal(), $tip->reveal(), $this->dateTime))
            ->shouldNotBeCalled()
        ;
        $this->tipOpenedRepository->set(new TipOpened($this->user->reveal(), $tip->reveal(), $this->dateTime))->shouldBeCalled();

        $this->assertTrue(
            $this->isTipOpened->isSatisfiedBy(
                new TipTranslationViewQuery($this->sheet->reveal(), $this->user->reveal(), 'my-context', 'fr'),
                new TipTranslationView(42, 'Info', 'Some content', 'My info', Tip::DISPLAY_ALWAYS_OPENED)
            )
        );
    }
}
