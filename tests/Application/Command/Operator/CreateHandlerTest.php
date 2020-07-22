<?php

namespace Proximum\Vimeet\Tests\Application\Command\Operator;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Command\Operator\Create;
use Proximum\Vimeet\Application\Command\Operator\CreateHandler;
use Proximum\Vimeet\Application\Components\Token\Admin\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Admin\ActivateAccountEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandleWithEvents(): void
    {
        $dateTime  = new \DateTime();
        $event = EventFactory::createEvent();
        $event2 = EventFactory::createEvent();
        $organizer = new Admin('test@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_ORGANIZER, $dateTime);
        $organizer->addEvent($event);
        $organizer->addEvent($event2);

        $command = new Create($organizer);
        $command->email = 'test2@test.com';
        $command->password = 'password';
        $command->firstname = 'toto';
        $command->lastname = 'tata';
        $command->events = [$event, $event2];

        $operator = new Admin('test2@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR, $dateTime);
        $expectedOperator = new Admin('test2@test.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR, $dateTime);
        $expectedOperator->addEvent($event);
        $expectedOperator->addEvent($event2);

        $saltGenerator = $this->prophesize(SaltGeneratorInterface::class);
        $saltGenerator->generate()->shouldBeCalled()->willReturn('__salt__');

        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $passwordEncoder->encode(Argument::that(static function (Admin $user) use ($operator) {
            return $user->getEmail() === $operator->getEmail();
        }), $command->password)->shouldBeCalled()->willReturn('encoded_password');

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldBeCalled()->willReturn(false);
        $adminRepository->add($expectedOperator)->shouldBeCalled();

        $activateAccountTokenGenerator  = $this->prophesize(ActivateAccountTokenGenerator::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $expectedActivateAccountToken = new ActivateAccountToken(
            $expectedOperator,
            'STRING',
            $dateTime
        );

        $activateAccountEvent = new ActivateAccountEvent(
            $expectedOperator,
            $expectedActivateAccountToken,
            'fr'
        );

        $activateAccountTokenGenerator->generate($expectedOperator)->shouldBeCalled()->willReturn($expectedActivateAccountToken);
        $eventDispatcher->dispatch(Events::ADMIN_ACCOUNT_ACTIVATED, $activateAccountEvent)->shouldBeCalled();

        $handler = new CreateHandler(
            $adminRepository->reveal(),
            $passwordEncoder->reveal(),
            $saltGenerator->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $eventDispatcher->reveal(),
            $dateTime
        );
        $handler->handle($command);
    }
}
