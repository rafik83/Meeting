<?php

namespace Proximum\Vimeet\Tests\Application\Command\Admin;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Admin\Create;
use Proximum\Vimeet\Application\Command\Admin\CreateHandler;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $dateTime = new \DateTime();
        $command = new Create('fr');
        $command->email = 'test@test.com';
        $command->password = 'password';
        $command->firstname = 'toto';
        $command->lastname = 'tata';
        $command->role = Admin::ROLE_ORGANIZER;
        $command->events = [];

        $admin = new Admin('test@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_ORGANIZER, $dateTime);
        $expectedAdmin = new Admin('test@test.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_ORGANIZER, $dateTime);

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__salt__');

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode(Argument::that(static function (Admin $user) use ($admin) {
            return $user->getEmail() === $admin->getEmail();
        }), $command->password)->shouldBeCalled()->willReturn('encoded_password');

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldBeCalled()->willReturn(false);
        $adminRepository->add($expectedAdmin)->shouldBeCalled();

        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal(),
            $dateTime
        );
        $handler->handle($command);
    }

    public function testHandleWithEvents(): void
    {
        $event = EventFactory::createEvent();
        $event2 = EventFactory::createEvent();
        $dateTime = new \DateTime();
        $command = new Create('fr');
        $command->email = 'test@test.com';
        $command->password = 'password';
        $command->firstname = 'toto';
        $command->lastname = 'tata';
        $command->role = Admin::ROLE_ORGANIZER;
        $command->events = [
            0 => $event,
            1 => $event2,
        ];

        $admin = new Admin('test@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_ORGANIZER, $dateTime);
        $expectedAdmin = new Admin('test@test.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_ORGANIZER, $dateTime);
        $expectedAdmin->addEvent($event);
        $expectedAdmin->addEvent($event2);

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__salt__');

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode(Argument::that(function (Admin $user) use ($admin) {
            return $user->getEmail() === $admin->getEmail();
        }), $command->password)->shouldBeCalled()->willReturn('encoded_password');

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldBeCalled()->willReturn(false);
        $adminRepository->add($expectedAdmin)->shouldBeCalled();

        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal(),
            $dateTime
        );
        $handler->handle($command);
    }

    public function testEmailAlreadyExistsException(): void
    {
        $this->expectException(EmailAlreadyExistsException::class);

        $dateTime = new \DateTime();
        $command = new Create('fr');
        $command->email = 'test@test.com';
        $command->password = 'password';
        $command->firstname = 'toto';
        $command->lastname = 'tata';
        $command->role = Admin::ROLE_ORGANIZER;
        $command->events = [];

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate(Argument::Any())->shouldNotBeCalled();

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode(Argument::Any())->shouldNotBeCalled();

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldBeCalled()->willReturn(true);
        $adminRepository->add(Argument::Any())->shouldNotBeCalled();

        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal(),
            $dateTime
        );
        $handler->handle($command);
    }
}
