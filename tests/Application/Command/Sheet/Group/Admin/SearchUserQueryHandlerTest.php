<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Sheet\Group\Admin;

use Proximum\Vimeet\Application\Command\Sheet\Group\SearchUser;
use Proximum\Vimeet\Application\Command\Sheet\Group\SearchUserHandler;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;
use Proximum\Vimeet\Domain\View\Group\UserView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class SearchUserQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $user  = UserFactory::create('p.seb@elao.com');
        $event = EventFactory::createEvent('Event');

        $userRepository       = $this->prophesize(UserRepositoryInterface::class);
        $userToManagerChecker = $this->prophesize(UserToGroupManagerChecker::class);

        $expectedView = new UserView(null, 'p.seb@elao.com', 'p.seb@elao.com');

        $command = new SearchUser($event);
        $command->email = 'p.seb@elao.com';

        $handler = new SearchUserHandler($userRepository->reveal(), $userToManagerChecker->reveal());

        $userRepository->findByEmail($command->email)->shouldBeCalled()->willReturn($user);
        $userToManagerChecker->isUserToGroupManagerAllowed($event, $user)->shouldBeCalled()->willReturn(true);

        $resultView = $handler->handle($command);

        $this->assertEquals($expectedView, $resultView);
    }
}
