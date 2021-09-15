<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Security\Voter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Security\AdminSheetAccess;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\Authorization;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminUserEventAccessVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AdminUserEventAccessVoterTest extends TestCase
{
    /** @var ObjectProphecy */
    private $adminSheetAccess;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $admin;

    /** @var ObjectProphecy */
    private $token;

    public function setUp()
    {
        $this->adminSheetAccess = $this->prophesize(AdminSheetAccess::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $this->admin = $this->prophesize(Admin::class);
        $this->token = $this->prophesize(TokenInterface::class);
    }

    public function testVoteOnAttributeGrantAccess()
    {
        $this->token->getUser()->willReturn($this->admin->reveal());
        $event = $this->prophesize(Event::class);
        $userToTest = $this->prophesize(User::class);
        $userEvent = new Authorization($userToTest->reveal(), $event->reveal());
        $sheet = $this->prophesize(Sheet::class);
        $this->sheetRepository->getSheetsByUserAndEvent($userToTest->reveal(), $event->reveal())->shouldBeCalled()->willReturn([$sheet->reveal()]);

        $this->adminSheetAccess->canAccess($this->admin->reveal(), $sheet->reveal())->shouldBeCalled()->willReturn(true);

        $accessVoter = new AdminUserEventAccessVoter($this->adminSheetAccess->reveal(), $this->sheetRepository->reveal());

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $accessVoter->vote($this->token->reveal(), $userEvent, ['PERMISSION_USER_ACCESS']));
    }

    public function testVoteOnAttributeDenyAccess()
    {
        $this->token->getUser()->willReturn($this->admin->reveal());
        $event = $this->prophesize(Event::class);
        $userToTest = $this->prophesize(User::class);
        $userEvent = new Authorization($userToTest->reveal(), $event->reveal());
        $sheet = $this->prophesize(Sheet::class);
        $this->sheetRepository->getSheetsByUserAndEvent($userToTest->reveal(), $event->reveal())->shouldBeCalled()->willReturn([$sheet->reveal()]);

        $this->adminSheetAccess->canAccess($this->admin->reveal(), $sheet->reveal())->shouldBeCalled()->willReturn(false);

        $accessVoter = new AdminUserEventAccessVoter($this->adminSheetAccess->reveal(), $this->sheetRepository->reveal());

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $accessVoter->vote($this->token->reveal(), $userEvent, ['PERMISSION_USER_ACCESS']));
    }

    public function testVoteOnAttributeDenyAccessWhenCurrentUserIsNotAdmin()
    {
        $this->token->getUser()->willReturn($this->prophesize(User::class));
        $event = $this->prophesize(Event::class);
        $userToTest = $this->prophesize(User::class);
        $userEvent = new Authorization($userToTest->reveal(), $event->reveal());
        $sheet = $this->prophesize(Sheet::class);
        $this->sheetRepository->getSheetsByUserAndEvent($userToTest->reveal(), $event->reveal())->shouldNotBeCalled();

        $this->adminSheetAccess->canAccess($this->admin->reveal(), $sheet->reveal())->shouldNotBeCalled();

        $accessVoter = new AdminUserEventAccessVoter($this->adminSheetAccess->reveal(), $this->sheetRepository->reveal());

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $accessVoter->vote($this->token->reveal(), $userEvent, ['PERMISSION_USER_ACCESS']));
    }

    public function testVoteOnAttributeDenyAccessWhenUserToTestHasNoSheet()
    {
        $this->token->getUser()->willReturn($this->admin->reveal());
        $event = $this->prophesize(Event::class);
        $userToTest = $this->prophesize(User::class);
        $userEvent = new Authorization($userToTest->reveal(), $event->reveal());
        $sheet = $this->prophesize(Sheet::class);
        $this->sheetRepository->getSheetsByUserAndEvent($userToTest->reveal(), $event->reveal())->shouldBeCalled()->willReturn([]);

        $this->adminSheetAccess->canAccess($this->admin->reveal(), $sheet->reveal())->shouldNotBeCalled();

        $accessVoter = new AdminUserEventAccessVoter($this->adminSheetAccess->reveal(), $this->sheetRepository->reveal());

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $accessVoter->vote($this->token->reveal(), $userEvent, ['PERMISSION_USER_ACCESS']));
    }
}
