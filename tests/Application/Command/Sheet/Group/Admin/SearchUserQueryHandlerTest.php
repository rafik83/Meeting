<?php

namespace Application\Command\Sheet\Group\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\Group\SearchUser;
use Proximum\Vimeet\Application\Command\Sheet\Group\SearchUserHandler;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;
use Proximum\Vimeet\Domain\View\Group\UserView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class SearchUserQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $user  = UserFactory::create('patrick.sebastien@example.com');
        $event = EventFactory::createEvent('Event');

        $userRepository       = $this->prophesize(UserRepositoryInterface::class);
        $userToManagerChecker = $this->prophesize(UserToGroupManagerChecker::class);

        $expectedView = new UserView(1, 'patrick.sebastien@example.com', ' ');

        $command = new SearchUser($event);
        $command->email = 'patrick.sebastien@example.com';

        $handler = new SearchUserHandler($userRepository->reveal(), $userToManagerChecker->reveal());

        $userRepository->findByEmail($command->email)->shouldBeCalled()->willReturn($user);
        $userToManagerChecker->isUserToGroupManagerAllowed($event, $user)->shouldBeCalled()->willReturn(true);

        $resultView = $handler->handle($command);

        $this->assertEquals($expectedView, $resultView);
    }
}
