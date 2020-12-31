<?php

namespace Proximum\Vimeet\Tests\Application\Command\User;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Register\RegisterNewUser;
use Proximum\Vimeet\Application\Command\Register\RegisterNewUserHandler;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RegisterHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event             = EventFactory::createEvent();
        $type              = new TypeView(1, 'title', 'desc', false);
        $command           = new RegisterNewUser('test@test.com', 'fr', $event, $type);
        $command->password = 'password';

        $user         = new User('test@test.com', '__salt__', null, 'fr');
        $expectedUser = new User('test@test.com', '__salt__', 'encoded_password', 'fr');

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__salt__');

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode(Argument::that(function (User $u) use ($user) {
            return $u->getEmail() === $user->getEmail();
        }), $command->password)->shouldBeCalled()->willReturn('encoded_password');

        $userRepository  = $this->prophesize(UserRepositoryInterface::class);
        $userEventRepository  = $this->prophesize(UserEventRepositoryInterface::class);

        $userRepository->emailExists($command->email)->shouldBeCalled()->willReturn(false);
        $userRepository->add($expectedUser)->shouldBeCalled();

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $handler = new RegisterNewUserHandler(
            $userRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal(),
            $userEventRepository->reveal(),
            $typeRepository->reveal()
        );

        $handler->handle($command);
    }

    public function testEmailAlreadyExistsException()
    {
        $this->expectException(EmailAlreadyExistsException::class);

        $event             = EventFactory::createEvent();
        $type              = new TypeView(1, 'title', 'desc', false);
        $command           = new RegisterNewUser('test@test.com', 'fr', $event, $type);
        $command->password = 'password';

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldNotBeCalled();

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode()->shouldNotBeCalled();

        $userRepository      = $this->prophesize(UserRepositoryInterface::class);
        $userEventRepository = $this->prophesize(UserEventRepositoryInterface::class);

        $userRepository->emailExists($command->email)->shouldBeCalled()->willReturn(true);
        $userRepository->add()->shouldNotBeCalled();

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $handler = new RegisterNewUserHandler(
            $userRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal(),
            $userEventRepository->reveal(),
            $typeRepository->reveal()
        );
        $handler->handle($command);
    }
}
