<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Phone;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\User\Phone\ValidateCode;
use Proximum\Vimeet\Application\Command\User\Phone\ValidateCodeHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\Phone\PhoneValidatedEvent;
use Proximum\Vimeet\Application\Exception\User\Phone\CodeAlreadyValidatedException;
use Proximum\Vimeet\Application\Exception\User\Phone\CodeNotValidException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class ValidateCodeHandlerTest extends TestCase
{
    public function testHandleCodeNotValid()
    {
        $this->expectException(CodeNotValidException::class);

        $dateTime = new \DateTime();
        $userEventPhone = $this->prophesize(UserEventPhone::class);
        $userEventPhone->getCode()->willReturn('1234');
        $userEventPhone->isValidated()->willReturn(false);

        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $userEventPhoneRepository->set($userEventPhone->reveal())->shouldNotBeCalled();

        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $delayedEventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $command = new ValidateCode($userEventPhone->reveal());
        $command->code = '4321';
        $handler = new ValidateCodeHandler(
            $delayedEventDispatcher->reveal(),
            $userEventPhoneRepository->reveal(),
            $dateTime
        );
        $handler->handle($command);
    }

    public function testHandleUserEventPhoneAlreadyValidated()
    {
        $this->expectException(CodeAlreadyValidatedException::class);

        $dateTime = new \DateTime();
        $userEventPhone = $this->prophesize(UserEventPhone::class);
        $userEventPhone->isValidated()->willReturn(true);

        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $userEventPhoneRepository->set($userEventPhone->reveal())->shouldNotBeCalled();

        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $delayedEventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $command = new ValidateCode($userEventPhone->reveal());
        $handler = new ValidateCodeHandler(
            $delayedEventDispatcher->reveal(),
            $userEventPhoneRepository->reveal(),
            $dateTime
        );
        $handler->handle($command);
    }

    public function testHandle()
    {
        $dateTime = new \DateTime();
        $userEventPhone = $this->prophesize(UserEventPhone::class);
        $userEventPhone->isValidated()->willReturn(false);
        $userEventPhone->getCode()->willReturn('1234');
        $userEventPhone->validate($dateTime)->shouldBeCalled();
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $userEventPhone->getUser()->willReturn($user->reveal());
        $userEventPhone->getEvent()->willReturn($event->reveal());

        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $userEventPhoneRepository->set($userEventPhone->reveal())->shouldBeCalled();

        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $delayedEventDispatcher
            ->dispatch(
                Events::USER_PHONE_VALIDATED,
                new PhoneValidatedEvent($user->reveal(), $event->reveal())
            )
            ->shouldBeCalled()
        ;

        $command = new ValidateCode($userEventPhone->reveal());
        $command->code = '1234';
        $handler = new ValidateCodeHandler(
            $delayedEventDispatcher->reveal(),
            $userEventPhoneRepository->reveal(),
            $dateTime
        );
        $handler->handle($command);
    }
}
