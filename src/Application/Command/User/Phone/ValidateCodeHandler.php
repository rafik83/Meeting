<?php

namespace Proximum\Vimeet\Application\Command\User\Phone;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\Phone\PhoneValidatedEvent;
use Proximum\Vimeet\Application\Exception\User\Phone\CodeAlreadyValidatedException;
use Proximum\Vimeet\Application\Exception\User\Phone\CodeNotValidException;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class ValidateCodeHandler
{
    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * @param DelayedEventDispatcherInterface   $delayedEventDispatcher
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->userEventPhoneRepository = $userEventPhoneRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param ValidateCode $command
     *
     * @throws CodeAlreadyValidatedException
     * @throws CodeNotValidException
     */
    public function handle(ValidateCode $command)
    {
        if ($command->userEventPhone->isValidated()) {
            throw new CodeAlreadyValidatedException('The UserEventPhone is already validated');
        }

        if ($command->code !== $command->userEventPhone->getCode()) {
            throw new CodeNotValidException('The given code is not valid');
        }

        $command->userEventPhone->validate($this->dateTime);
        $this->userEventPhoneRepository->set($command->userEventPhone);

        $this->delayedEventDispatcher->dispatch(
            Events::USER_PHONE_VALIDATED,
            new PhoneValidatedEvent($command->userEventPhone->getUser(), $command->userEventPhone->getEvent())
        );
    }
}
