<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\ImpersonatingUserCheckerInterface;
use Proximum\Vimeet\Application\Command\Sheet\AddView;
use Proximum\Vimeet\Application\Command\Sheet\AddViewHandler;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AddViewHandlerTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy|User */
    private $user;

    /** @var \Prophecy\Prophecy\ObjectProphecy|Sheet */
    private $sheet;

    /** @var \Prophecy\Prophecy\ObjectProphecy|SheetRepositoryInterface */
    private $sheetRepository;

    /** @var \Prophecy\Prophecy\ObjectProphecy|ImpersonatingUserCheckerInterface */
    private $impersonatingUserChecker;

    /** @var \Prophecy\Prophecy\ObjectProphecy|Sheet\Analytics */
    private $analytics;

    protected function setUp(): void
    {
        $this->user = $this->prophesize(User::class);

        $this->analytics = $this->prophesize(Sheet\Analytics::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getAnalytics()->willReturn($this->analytics->reveal());
        $this->sheet->getUserParticipant($this->user->reveal())->willReturn(null);

        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->impersonatingUserChecker = $this->prophesize(ImpersonatingUserCheckerInterface::class);
    }

    public function testNoTrackWhenImpersonatingUser(): void
    {
        $this->impersonatingUserChecker->isImpersonated()->shouldBeCalled()->willReturn(true);
        $this->analytics->incrementViews(Argument::any())->shouldNotBeCalled();

        $query = new AddView($this->user->reveal(), $this->sheet->reveal());

        $handler = new AddViewHandler($this->sheetRepository->reveal(), $this->impersonatingUserChecker->reveal());
        $handler->handle($query);
    }

    public function testNoTrackWhenViewingMySheet(): void
    {
        $this->impersonatingUserChecker->isImpersonated()->shouldBeCalled()->willReturn(false);
        $this->analytics->incrementViews(Argument::any())->shouldNotBeCalled();

        $participant = $this->prophesize(Participant::class);
        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($participant->reveal());

        $query = new AddView($this->user->reveal(), $this->sheet->reveal());

        $handler = new AddViewHandler($this->sheetRepository->reveal(), $this->impersonatingUserChecker->reveal());
        $handler->handle($query);
    }

    public function testTrack(): void
    {
        $this->impersonatingUserChecker->isImpersonated()->shouldBeCalled()->willReturn(false);

        $this->analytics->incrementViews($this->user->reveal())->shouldBeCalled();
        $this->sheetRepository->set($this->sheet->reveal())->shouldBeCalled();

        $query = new AddView($this->user->reveal(), $this->sheet->reveal());

        $handler = new AddViewHandler($this->sheetRepository->reveal(), $this->impersonatingUserChecker->reveal());
        $handler->handle($query);
    }
}
