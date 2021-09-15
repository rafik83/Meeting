<?php

namespace Proximum\Vimeet\Application\Command\Token\UserEventToken;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\Agenda\AgendaConfirmationEvent;
use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenUnexpectedTypeException;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;

class ConfirmAgendaHandler
{
    const ALREADY_CONFIRMED = 'already_confirmed';
    const CONFIRMED = 'confirmed';

    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * @param UserEventTokenRepositoryInterface $userEventTokenRepository
     * @param DelayedEventDispatcherInterface   $delayedEventDispatcher
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        UserEventTokenRepositoryInterface $userEventTokenRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->userEventTokenRepository = $userEventTokenRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->dateTime = $dateTime;
    }

    /**
     * @param ConfirmAgenda $command
     *
     * @throws UserEventTokenUnexpectedTypeException
     *
     * @return string
     */
    public function handle(ConfirmAgenda $command)
    {
        if ($command->userEventToken->isConfirmed()) {
            return self::ALREADY_CONFIRMED;
        }

        if (!$command->userEventToken->isAgendaConfirmation()) {
            throw new UserEventTokenUnexpectedTypeException();
        }

        $command->userEventToken->confirm($this->dateTime);

        $this->userEventTokenRepository->set($command->userEventToken);

        $this->delayedEventDispatcher->dispatch(
            Events::USER_AGENDA_CONFIRMED,
            new AgendaConfirmationEvent($command->userEventToken->getEvent(), $command->userEventToken->getUser())
        );

        return self::CONFIRMED;
    }
}
