<?php

namespace Proximum\Vimeet\Application\Command\User\Event\Token;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\EventToken\AgendaConfirmationStatusUpdated;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenGenerator;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;
use Proximum\Vimeet\Domain\User\Event\AgendaConfirmation\Constant;

class UpdateAgendaConfirmationHandler
{
    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /** @var UserEventTokenGenerator */
    private $userEventTokenGenerator;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * @param DelayedEventDispatcherInterface   $delayedEventDispatcher
     * @param UserEventTokenRepositoryInterface $userEventTokenRepository
     * @param UserEventTokenGenerator           $userEventTokenGenerator
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        UserEventTokenRepositoryInterface $userEventTokenRepository,
        UserEventTokenGenerator $userEventTokenGenerator,
        \DateTimeInterface $dateTime
    ) {
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->userEventTokenRepository = $userEventTokenRepository;
        $this->userEventTokenGenerator = $userEventTokenGenerator;
        $this->dateTime = $dateTime;
    }

    /**
     * @param UpdateAgendaConfirmation $command
     */
    public function handle(UpdateAgendaConfirmation $command): void
    {
        $token = $this->userEventTokenGenerator->getUserEventTokenForConfirmAgenda(
            $command->event,
            $command->user,
            UserEventTokenType::AGENDA_CONFIRMATION
        );

        if (Constant::AGENDA_CONFIRMED === $command->status) {
            $token->confirm($this->dateTime);
        } elseif (Constant::AGENDA_NOT_CONFIRMED === $command->status) {
            $token->unConfirm();
        }

        $this->userEventTokenRepository->set($token);

        $this->delayedEventDispatcher->dispatch(
            Events::USER_AGENDA_CONFIRMATION_STATUS_UPDATED,
            new AgendaConfirmationStatusUpdated($command->event, $command->user)
        );
    }
}
