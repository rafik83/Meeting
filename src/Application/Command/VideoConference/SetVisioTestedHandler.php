<?php

namespace Proximum\Vimeet\Application\Command\VideoConference;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantVisioTestedEvent;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class SetVisioTestedHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $userEventExtraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        ExtraDataRepositoryInterface $userEventExtraDataRepository,
        \DateTimeInterface $dateTime,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->dateTime = $dateTime;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    public function handle(SetVisioTested $setVisioTested): void
    {
        $visioTestedExtraData = $this->userEventExtraDataRepository->getExtraDataForEventNameAndUser(
            $setVisioTested->event,
            Type::VISIO_TESTED,
            $setVisioTested->user
        );

        if (!$visioTestedExtraData instanceof ExtraData) {
            $this->delayedEventDispatcher->dispatch(
                Events::PARTICIPANT_VISIO_TESTED,
                new ParticipantVisioTestedEvent($setVisioTested->user, $setVisioTested->event)
            );

            $this->userEventExtraDataRepository->add(
                new ExtraData(
                    $setVisioTested->user,
                    $setVisioTested->event,
                    Type::VISIO_TESTED,
                    '1',
                    $this->dateTime
                )
            );
        }
    }
}
