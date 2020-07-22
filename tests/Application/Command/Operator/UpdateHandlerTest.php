<?php

namespace Proximum\Vimeet\Tests\Application\Command\Operator;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Operator\Update;
use Proximum\Vimeet\Application\Command\Operator\UpdateHandler;
use Proximum\Vimeet\Application\Components\Token\Admin\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Admin\ActivateAccountEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $dateTime  = new \DateTime();
        $event = EventFactory::createEvent('a');
        $event2 = EventFactory::createEvent('b');
        $event3 = EventFactory::createEvent('c');
        $event4 = EventFactory::createEvent('d');
        $operator = new Admin('test2@test.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR, $dateTime);
        $operator->addEvent($event);
        $operator->addEvent($event2);
        $operator->addEvent($event4);
        $events = [
            $event,
            $event2,
            $event3,
        ];

        $command = new Update($operator, $events);
        $command->email = 'test2@test.com';
        $command->firstname = 'truc';
        $command->lastname = 'muche';
        $command->events = [$event, $event3];

        $expectedOperator = new Admin('test2@test.com', '__salt__', 'encoded_password', 'fr', 'truc', 'muche', Admin::ROLE_OPERATOR, $dateTime);
        $expectedOperator->addEvent($event);
        $expectedOperator->addEvent($event3);
        $expectedOperator->addEvent($event4);

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldNotBeCalled();
        $adminRepository->set($expectedOperator)->shouldBeCalled();

        $activateAccountTokenGenerator  = $this->prophesize(ActivateAccountTokenGenerator::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

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

        $activateAccountTokenGenerator->generate($expectedOperator)->shouldNotBeCalled();
        $eventDispatcher->dispatch('admin_activate_account', $activateAccountEvent)->shouldNotBeCalled();

        $handler = new UpdateHandler(
            $adminRepository->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($command);
    }

    public function testHandleWithNewMail(): void
    {
        $dateTime  = new \DateTime();
        $event = EventFactory::createEvent('a');
        $event2 = EventFactory::createEvent('b');
        $event3 = EventFactory::createEvent('c');
        $operator = new Admin('test@test.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_OPERATOR, $dateTime);
        $operator->addEvent($event);
        $operator->addEvent($event2);

        $command = new Update($operator, [$event, $event2, $event3]);
        $command->email = 'test2@test.com';
        $command->firstname = 'truc';
        $command->lastname = 'muche';
        $command->events = [$event, $event3];

        $expectedOperator = new Admin('test2@test.com', '__salt__', 'encoded_password', 'fr', 'truc', 'muche', Admin::ROLE_OPERATOR, $dateTime);
        $expectedOperator->addEvent($event);
        $expectedOperator->addEvent($event3);

        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $adminRepository->emailExists($command->email)->shouldBeCalled()->willReturn(false);
        $adminRepository->set($expectedOperator)->shouldBeCalled();

        $activateAccountTokenGenerator = $this->prophesize(ActivateAccountTokenGenerator::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

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

        $handler = new UpdateHandler(
            $adminRepository->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($command);
    }
}
