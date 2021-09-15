<?php

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class MeetingPlanningModifiedCommandHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(MeetingPlanningModifiedCommand $command): void
    {
        if ($this->extraDataRepository->getExtraDataForEventNameAndUser(
                $command->event,
                Type::PLANNING_MODIFIED,
                $command->user
            ) instanceof ExtraData
        ) {
            return;
        }

        $this->extraDataRepository->add(new ExtraData(
            $command->user,
            $command->event,
            Type::PLANNING_MODIFIED,
            null,
            $this->dateTime
        ));
    }
}
