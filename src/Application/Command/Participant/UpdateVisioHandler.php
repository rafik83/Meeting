<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantVisioToggledEvent;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class UpdateVisioHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->dateTime = $dateTime;
    }

    public function handle(UpdateVisio $updateVisio): void
    {
        $event = $updateVisio->participant->getEvent();
        $user = $updateVisio->participant->getUser();

        $extraData = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $event,
            Type::IS_PARTICIPANT_VISIO,
            $user
        );

        if (true === $updateVisio->visio && !$extraData instanceof ExtraData) {
            $this->extraDataRepository->add(
                new ExtraData(
                    $user,
                    $event,
                    Type::IS_PARTICIPANT_VISIO,
                    true,
                    $this->dateTime
                )
            );
        }

        if (false === $updateVisio->visio && $extraData instanceof ExtraData) {
            $this->extraDataRepository->remove($extraData);
        }

        $this->delayedEventDispatcher->dispatch(
            Events::PARTICIPANT_VISIO_TOGGLED,
            new ParticipantVisioToggledEvent($updateVisio->participant, $updateVisio->visio)
        );
    }
}
