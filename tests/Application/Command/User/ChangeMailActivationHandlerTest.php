<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\User;

use DateTime;
use Proximum\Vimeet\Application\Command\User\ChangeMailActivation;
use Proximum\Vimeet\Application\Command\User\ChangeMailActivationHandler;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChangeMailTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ChangeMailActivationHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Base
        $date = new DateTime;
        $user = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');

        // Actual
        $changeMailToken      = new ChangeMailToken($user, 'toto@toto.fr', '1234567890', $date);
        $changeMailActivation = new ChangeMailActivation($changeMailToken);

        // Expected
        $expectedUser = new User('toto@toto.fr', '__SALT__', '__TEST__', 'fr');

        // Mock
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->set($expectedUser)->shouldBeCalled();

        $changeMailTokenRepository = $this->prophesize(ChangeMailTokenRepositoryInterface::class);
        $changeMailTokenRepository->deleteAllForUser($user)->shouldBeCalled();

        // Handler
        $handler = new ChangeMailActivationHandler($userRepository->reveal(), $changeMailTokenRepository->reveal());
        $handler->handle($changeMailActivation);
    }
}
