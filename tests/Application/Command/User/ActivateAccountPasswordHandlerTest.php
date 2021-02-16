<?php

namespace Proximum\Vimeet\Tests\Application\Command\User;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\User\ActivateAccountPassword;
use Proximum\Vimeet\Application\Command\User\ActivateAccountPasswordHandler;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\ActivateAccountTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ActivateAccountPasswordHandlerTest extends TestCase
{
    public function testHandle()
    {
        $user  = new User('test@test.fr', '__OLDSALT__', '__OLD__', 'fr');
        $event = EventFactory::createEvent();

        $sheet       = SheetFactory::create($event, $user);
        $participant = ParticipantFactory::create($sheet, $user);
        $participant->setActive(true);

        // Expected
        $expectedUser = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');

        // Mock
        $userRepository                 = $this->prophesize(UserRepositoryInterface::class);
        $encoder                        = $this->prophesize(PasswordEncoderInterface::class);
        $saltGenerator                  = $this->prophesize(SaltGeneratorInterface::class);
        $activateAccountTokenRepository = $this->prophesize(ActivateAccountTokenRepositoryInterface::class);
        $participantRepository          = $this->prophesize(ParticipantRepositoryInterface::class);

        $saltGenerator->generate()->shouldBeCalled()->willReturn('__SALT__');
        $encoder->encode(Argument::that(function (User $encodedUser) use ($user) {
            return $user->getEmail() === $encodedUser->getEmail();
        }), 'TOTO')->shouldBeCalled()->willReturn('__TEST__');
        $userRepository->set($expectedUser)->shouldBeCalled();
        $activateAccountTokenRepository->deleteAllForUser($expectedUser)->shouldBeCalled();

        $activeAccountPassword = new ActivateAccountPassword($user, $sheet);
        $activeAccountPassword->password = 'TOTO';

        $participantRepository->set($participant)->shouldBeCalled();

        $handler = new ActivateAccountPasswordHandler(
            $userRepository->reveal(),
            $encoder->reveal(),
            $saltGenerator->reveal(),
            $activateAccountTokenRepository->reveal(),
            $participantRepository->reveal()
        );
        $handler->handle($activeAccountPassword);
    }
}
