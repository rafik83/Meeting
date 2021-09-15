<?php

namespace Proximum\Vimeet\Tests\Application\Command\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Admin\ChangePassword;
use Proximum\Vimeet\Application\Command\Admin\ChangePasswordHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class ChangePasswordHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime =  new \DateTime();
        $admin = new Admin('test@test.com', '__salt__', 'encoded_password', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN', $dateTime);

        // Command
        $command = new ChangePassword($admin);
        $command->plainPassword = 'new-password';

        $expectedUser = new Admin(
            'test@test.com',
            '__new_salt__',
            'encoded_new_password',
            'fr',
            'truc',
            'muche',
            'ROLE_SUPER_ADMIN',
            $dateTime
        );

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__new_salt__');

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder
            ->encode($admin, $command->plainPassword)
            ->shouldBeCalled()
            ->willReturn('encoded_new_password');

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->set($expectedUser)->shouldBeCalled();

        $handler = new ChangePasswordHandler(
            $adminRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal()
        );
        $handler->handle($command);
    }
}
