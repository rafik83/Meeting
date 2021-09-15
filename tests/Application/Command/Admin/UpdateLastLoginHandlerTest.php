<?php

namespace Proximum\Vimeet\Tests\Application\Command\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Admin\UpdateLastLogin;
use Proximum\Vimeet\Application\Command\Admin\UpdateLastLoginHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class UpdateLastLoginHandlerTest extends TestCase
{
    public function testHandleWithAdminNull()
    {
        $date = new \DateTime('2017-08-08 08:08:08.000');
        $email = 'test@email.com';

        $command = new UpdateLastLogin($email);

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->findByEmail($email)->shouldBeCalled()->willReturn(null);
        $adminRepository->set()->shouldNotBeCalled();

        $handler = new UpdateLastLoginHandler($adminRepository->reveal(), $date);
        $handler->handle($command);
    }

    public function testHandleWithAdminNotNull()
    {
        $dateTime = new \DateTime('2017-01-01 10:10:10.000');

        $email = 'toto@toto.fr';
        $date = new \DateTime('2017-08-08 08:08:08.000');

        $command = new UpdateLastLogin($email);

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

        $handler = new UpdateLastLoginHandler($adminRepository->reveal(), $date);
        $handler->handle($command);
    }
}
