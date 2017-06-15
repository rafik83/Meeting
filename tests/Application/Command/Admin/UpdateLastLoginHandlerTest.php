<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Admin;

use Proximum\Vimeet\Application\Command\Admin\UpdateLastLogin;
use Proximum\Vimeet\Application\Command\Admin\UpdateLastLoginHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class UpdateLastLoginHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWithAdminNull()
    {
        $date  = new \DateTime();
        $email = null;

        $command = new UpdateLastLogin($email, $date);

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->findByEmail(email)->shouldBeCalled()->willReturn(null);
        $adminRepository->set()->shouldNotBeCalled();

        $handler = new UpdateLastLoginHandler($adminRepository->reveal());
        $handler->handle($command);
    }

    public function testHandleWithAdminNotNull()
    {
        $dateTime = new \DateTime();

        $email = 'toto@toto.fr';
        $date  = new \DateTime();

        $command = new UpdateLastLogin($email, $date);

        $admin = new Admin(
            'test@test.com',
            '__salt__',
            'encoded_password',
            'fr',
            'toto',
            'tata',
            Admin::ROLE_ORGANIZER,
            $dateTime
        );

        $expectedAdmin = new Admin(
            'test@test.com',
            '__salt__',
            'encoded_password',
            'fr',
            'toto',
            'tata',
            Admin::ROLE_ORGANIZER,
            $dateTime
        );
        $expectedAdmin->setLastLoginAt($date);

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->findByEmail($email)->shouldBeCalled()->willReturn($admin);
        $adminRepository->set($expectedAdmin)->shouldBeCalled();

        $handler = new UpdateLastLoginHandler($adminRepository->reveal());
        $handler->handle($command);

    }

}
