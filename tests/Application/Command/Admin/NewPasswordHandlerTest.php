<?php

namespace Proximum\Vimeet\Tests\Application\Command\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Admin\NewPassword;
use Proximum\Vimeet\Application\Command\Admin\NewPasswordHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\Admin\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class NewPasswordHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime          = new \DateTime();
        $admin             = new Admin('test@test.fr', 'test', 'test', 'fr', 'jean', 'paul', 'ROLE_ADMIN', $dateTime);
        $expectedAdmin     = new Admin('test@test.fr', 'test', 'tatatata', 'fr', 'jean', 'paul', 'ROLE_ADMIN', $dateTime);
        $command           = new NewPassword($admin);
        $command->password = 'totototo';

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->set($expectedAdmin)->shouldBeCalled();

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('test');

        $encoder = $this->prophesize(PasswordEncoderInterface::class);
        $encoder->encode($admin, $command->password)->shouldBeCalled()->willReturn('tatatata');

        $forgottenPasswordToken = $this->prophesize(ForgottenPasswordTokenRepositoryInterface::class);
        $forgottenPasswordToken->deleteAllForUser($admin)->shouldBeCalled();

        $handler = new NewPasswordHandler(
            $adminRepository->reveal(),
            $encoder->reveal(),
            $saltGenerator->reveal(),
            $forgottenPasswordToken->reveal()
        );
        $handler->handle($command);
    }
}
