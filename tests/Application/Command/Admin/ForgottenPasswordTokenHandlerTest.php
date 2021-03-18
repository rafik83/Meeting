<?php

namespace Proximum\Vimeet\Tests\Application\Command\Admin;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Admin\ForgottenPassword;
use Proximum\Vimeet\Application\Command\Admin\ForgottenPasswordHandler;
use Proximum\Vimeet\Application\Components\Token\AdminForgottenPasswordTokenGenerator;
use Proximum\Vimeet\Application\Event\Admin\ResetPasswordEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Repository\Admin\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ForgottenPasswordTokenHandlerTest extends TestCase
{
    public function testHandle()
    {
        $command = new ForgottenPassword('fr');
        $command->email = 'test@test.fr';

        $dateTime               = new DateTime();
        $admin                  = new Admin('test@test.fr', 'test', 'test', 'fr', 'jean', 'paul', 'ROLE_ADMIN', $dateTime);
        $forgottenPasswordToken = new ForgottenPasswordToken($admin, 'token', $dateTime);
        $event                  = new ResetPasswordEvent($admin, $forgottenPasswordToken, $command->locale);

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->findByEmail($command->email)->shouldBeCalled()->willReturn($admin);

        $forgottenPasswordTokenGenerator = $this->prophesize(AdminForgottenPasswordTokenGenerator::class);
        $forgottenPasswordTokenGenerator->generate($admin)->shouldBeCalled()->willReturn(new ForgottenPasswordToken(
            $admin,
            'token',
            $dateTime
        ));

        $forgottenPasswordTokenRepository = $this->prophesize(ForgottenPasswordTokenRepositoryInterface::class);
        $forgottenPasswordTokenRepository->deleteAllForUser($admin)->shouldBeCalled();
        $forgottenPasswordTokenRepository->create($forgottenPasswordToken)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::ADMIN_PASSWORD_RESET, $event)->shouldBeCalled();

        $handler = new ForgottenPasswordHandler(
            $forgottenPasswordTokenGenerator->reveal(),
            $adminRepository->reveal(),
            $forgottenPasswordTokenRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testEmailDoesNotExistException()
    {
        $this->expectException(EmailDoesNotExistException::class);

        $command = new ForgottenPassword('fr');
        $command->email = 'test2@test.fr';

        $dateTime               = new DateTime();
        $admin                  = new Admin('test@test.fr', 'test', 'test', 'fr', 'jean', 'paul', 'ROLE_ADMIN', $dateTime);
        $forgottenPasswordToken = new ForgottenPasswordToken($admin, 'token', $dateTime);
        $event                  = new ResetPasswordEvent($admin, $forgottenPasswordToken, $command->locale);

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->findByEmail($command->email)->shouldBeCalled()->willReturn(null);

        $forgottenPasswordTokenGenerator = $this->prophesize(AdminForgottenPasswordTokenGenerator::class);
        $forgottenPasswordTokenGenerator->generate($admin)->shouldNotBeCalled();

        $forgottenPasswordTokenRepository = $this->prophesize(ForgottenPasswordTokenRepositoryInterface::class);
        $forgottenPasswordTokenRepository->deleteAllForUser($admin)->shouldNotBeCalled();
        $forgottenPasswordTokenRepository->create($forgottenPasswordToken)->shouldNotBeCalled();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch($event)->shouldNotBeCalled();

        $handler = new ForgottenPasswordHandler(
            $forgottenPasswordTokenGenerator->reveal(),
            $adminRepository->reveal(),
            $forgottenPasswordTokenRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($command);
    }
}
