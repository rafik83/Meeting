<?php

namespace Proximum\Vimeet\Tests\Application\Command\Admin;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Admin\Update;
use Proximum\Vimeet\Application\Command\Admin\UpdateHandler;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $dateTime = new \DateTime();
        $admin = new Admin(
            'test@test.com',
            '__salt__',
            null,
            'fr',
            'toto',
            'tata',
            Admin::ROLE_ORGANIZER,
            $dateTime
        );
        $command = new Update($admin);
        $command->email = 'test@test.com';
        $command->password = 'password2';
        $command->firstname = 'toto';
        $command->lastname = 'tata';
        $command->role = Admin::ROLE_ORGANIZER;
        $command->events = [];

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

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__salt__');

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode(Argument::that(function (Admin $user) use ($admin) {
            return $user->getEmail() === $admin->getEmail();
        }), $command->password)->shouldBeCalled()->willReturn('encoded_password');

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->set($expectedAdmin)->shouldBeCalled();

        $handler = new UpdateHandler(
            $adminRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal(),
            $dateTime
        );
        $handler->handle($command);
    }

    public function testHandleWithEvents(): void
    {
        $dateTime = new \DateTime();
        $event = EventFactory::createEvent();
        $event2 = EventFactory::createEvent();
        $event3 = EventFactory::createEvent();
        $admin = new Admin(
            'test@test.com',
            '__salt__',
            null,
            'fr',
            'toto',
            'tata',
            Admin::ROLE_ORGANIZER,
            $dateTime
        );
        $admin->addEvent($event);
        $admin->addEvent($event3);

        $command = new Update($admin);
        $command->email = 'test4@test.com';
        $command->password = 'password';
        $command->firstname = 'toto';
        $command->lastname = 'tata';
        $command->role = Admin::ROLE_ORGANIZER;
        $command->events = [
            0 => $event,
            1 => $event2,
        ];

        $expectedAdmin = new Admin(
            'test4@test.com',
            '__salt__',
            'encoded_password',
            'fr',
            'toto',
            'tata',
            Admin::ROLE_ORGANIZER,
            $dateTime
        );
        $expectedAdmin->addEvent($event);
        $expectedAdmin->addEvent($event2);

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__salt__');

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode(Argument::that(static function (Admin $user) use ($admin) {
            return $user->getEmail() === $admin->getEmail();
        }), $command->password)->shouldBeCalled()->willReturn('encoded_password');

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldBeCalled()->willReturn(false);
        $adminRepository->set($expectedAdmin)->shouldBeCalled();

        $handler = new UpdateHandler(
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
        $admin = new Admin(
            'test@test.com',
            '__salt__',
            null,
            'fr',
            'toto',
            'tata',
            Admin::ROLE_ORGANIZER,
            $dateTime
        );

        $command = new Update($admin);
        $command->email = 'test4@test.com';
        $command->password = 'password';
        $command->firstname = 'toto';
        $command->lastname = 'tata';
        $command->role = Admin::ROLE_ORGANIZER;
        $command->events = [];

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldNotBeCalled();

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode(Argument::any())->shouldNotBeCalled();

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldBeCalled()->willReturn(true);
        $adminRepository->add(Argument::any())->shouldNotBeCalled();

        $handler = new UpdateHandler(
            $adminRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal(),
            $dateTime
        );
        $handler->handle($command);
    }
}
