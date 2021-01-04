<?php

namespace Application\Command\Sheet\SheetViewed;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ImpersonatingUserCheckerInterface;
use Proximum\Vimeet\Application\Command\Sheet\SheetViewed\Add;
use Proximum\Vimeet\Application\Command\Sheet\SheetViewed\AddHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class AddHandlerTest extends TestCase
{
    /** @var Sheet */
    private $sheet;

    /** @var User */
    private $user;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var SheetRepositoryInterface */
    private $sheetViewedRepository;

    /** @var ImpersonatingUserCheckerInterface */
    private $impersonatingUserChecker;

    /** @var Add */
    private $command;

    /** @var AddHandler */
    private $handler;

    /** @var SheetViewed */
    private $sheetViewed;

    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();

        $this->sheet    = SheetFactory::create();
        $this->user     = UserFactory::create();
        $this->dateTime = new \DateTime();

        $this->sheetViewedRepository    = $this->prophesize(SheetViewedRepositoryInterface::class);
        $this->impersonatingUserChecker = $this->prophesize(ImpersonatingUserCheckerInterface::class);

        $this->command     = new Add($this->user, $this->sheet);
        $this->handler     = new AddHandler(
            $this->sheetViewedRepository->reveal(),
            $this->impersonatingUserChecker->reveal(),
            $this->dateTime
        );
        $this->sheetViewed = new SheetViewed($this->command->sheet, $this->command->user, $this->dateTime);
    }

    public function testHandle()
    {
        $this->impersonatingUserChecker->isImpersonated()->shouldBeCalled()->willReturn(false);
        $this->sheetViewedRepository->isSheetAlreadySeenByUser($this->user, $this->sheet)->shouldBeCalled()->willReturn(false);
        $this->sheetViewedRepository->add($this->sheetViewed)->shouldBeCalled();

        $this->handler->handle($this->command);
    }

    public function testWithSheetAlreadyViewed()
    {
        $this->impersonatingUserChecker->isImpersonated()->shouldBeCalled()->willReturn(false);
        $this->sheetViewedRepository->isSheetAlreadySeenByUser($this->user, $this->sheet)->shouldBeCalled()->willReturn(true);
        $this->sheetViewedRepository->add($this->sheetViewed)->shouldNotBeCalled();

        $this->handler->handle($this->command);
    }

    public function testWithImpersonatedUser()
    {
        $this->impersonatingUserChecker->isImpersonated()->shouldBeCalled()->willReturn(true);
        $this->sheetViewedRepository->isSheetAlreadySeenByUser($this->user, $this->sheet)->shouldNotBeCalled();
        $this->sheetViewedRepository->add($this->sheetViewed)->shouldNotBeCalled();

        $this->handler->handle($this->command);
    }
}
