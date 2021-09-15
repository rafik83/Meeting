<?php

namespace Domain\Service\SheetsGroup;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UserToManagerCheckerTest extends TestCase
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var Event */
    private $event;

    /** @var User */
    private $user;

    /** @var UserToGroupManagerChecker */
    private $userToManagerChecker;

    public function setUp()
    {
        $this->event                = EventFactory::createEvent('title');
        $this->user                 = UserFactory::create('test@elao.com');
        $this->groupRepository      = $this->prophesize(GroupRepositoryInterface::class);
        $this->sheetRepository      = $this->prophesize(SheetRepositoryInterface::class);
        $this->userToManagerChecker = new UserToGroupManagerChecker(
            $this->groupRepository->reveal(),
            $this->sheetRepository->reveal()
        );
    }

    public function testAllowedUserToGroupManager()
    {
        $this->groupRepository->getByEventAndManager($this->event, $this->user)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->sheetRepository->hasSheetWithGroupByUserByEvent($this->user, $this->event)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->assertEquals(
            true,
            $this->userToManagerChecker->isUserToGroupManagerAllowed($this->event, $this->user)
        );
    }
}
