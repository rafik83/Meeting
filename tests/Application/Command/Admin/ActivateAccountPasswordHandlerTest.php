<?php

namespace Proximum\Vimeet\Tests\Application\Command\Admin;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Admin\ActivateAccountPassword;
use Proximum\Vimeet\Application\Command\Admin\ActivateAccountPasswordHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\Admin\ActivateAccountTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class ActivateAccountPasswordHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $operator = new Admin('test2@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR, $dateTime);

        // Expected
        $expectedOperator = new Admin('test2@test.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR, $dateTime);

        // Mock
        $adminRepository                = $this->prophesize(AdminRepositoryInterface::class);
        $encoder                        = $this->prophesize(PasswordEncoderInterface::class);
        $saltGenerator                  = $this->prophesize(SaltGeneratorInterface::class);
        $activateAccountTokenRepository = $this->prophesize(ActivateAccountTokenRepositoryInterface::class);

        $saltGenerator->generate()->shouldBeCalled()->willReturn('__salt__');
        $encoder->encode(Argument::that(function (Admin $encodedOperator) use ($operator) {
            return $operator->getEmail() === $encodedOperator->getEmail();
        }), 'TOTO')->shouldBeCalled()->willReturn('encoded_password');
        $adminRepository->set($expectedOperator)->shouldBeCalled();
        $activateAccountTokenRepository->deleteAllForUser($expectedOperator)->shouldBeCalled();

        $activeAccountPassword = new ActivateAccountPassword($operator);
        $activeAccountPassword->password = 'TOTO';

        $handler = new ActivateAccountPasswordHandler(
            $adminRepository->reveal(),
            $encoder->reveal(),
            $saltGenerator->reveal(),
            $activateAccountTokenRepository->reveal()
        );
        $handler->handle($activeAccountPassword);
    }
}
