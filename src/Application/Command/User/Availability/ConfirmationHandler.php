<?php

namespace Proximum\Vimeet\Application\Command\User\Availability;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\Availability\ConfirmedEvent;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class ConfirmationHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * @param DelayedEventDispatcherInterface $delayedEventDispatcher
     * @param ExtraDataRepositoryInterface    $extraDataRepository
     * @param \DateTimeInterface              $dateTime
     */
    public function __construct(
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        ExtraDataRepositoryInterface $extraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->dateTime = $dateTime;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param Confirmation $command
     */
    public function handle(Confirmation $command): void
    {
        $availabilityConfirmation = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $command->event,
            Type::AVAILABILITY_CONFIRMATION,
            $command->user
        );

        if (null !== $availabilityConfirmation) {
            return;
        }

        $availabilityConfirmation = new ExtraData(
            $command->user,
            $command->event,
            Type::AVAILABILITY_CONFIRMATION,
            'confirmed',
            $this->dateTime
        );

        $this->extraDataRepository->add($availabilityConfirmation);

        $this->delayedEventDispatcher->dispatch(
            Events::USER_AVAILABILITY_CONFIRMED,
            new ConfirmedEvent($command->event, $command->user)
        );
    }
}
