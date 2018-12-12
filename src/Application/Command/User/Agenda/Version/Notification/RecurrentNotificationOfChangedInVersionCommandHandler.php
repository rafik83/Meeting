<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification;

use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class RecurrentNotificationOfChangedInVersionCommandHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var int */
    private $agendaVersionDiffNotificationTimeInMinutesParameters;

    /** @var int */
    private $agendaVersionDiffDDayNotificationTimeInMinutesParameters;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var NotifyUserOfChangedVersionCommandHandler */
    private $notifyUserOfChangedVersionCommandHandler;

    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        int $agendaVersionDiffNotificationTimeInMinutesParameters,
        int $agendaVersionDiffDDayNotificationTimeInMinutesParameters,
        NotifyUserOfChangedVersionCommandHandler $notifyUserOfChangedVersionCommandHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->agendaVersionDiffNotificationTimeInMinutesParameters = $agendaVersionDiffNotificationTimeInMinutesParameters;
        $this->agendaVersionDiffDDayNotificationTimeInMinutesParameters = $agendaVersionDiffDDayNotificationTimeInMinutesParameters;
        $this->notifyUserOfChangedVersionCommandHandler = $notifyUserOfChangedVersionCommandHandler;
        $this->dateTime = $dateTime;
    }

    public function handle(RecurrentNotificationOfChangedInVersionCommand $command): void
    {
        $date = (new \DateTime())->setTimestamp($this->dateTime->getTimestamp());
        $this->modifyDateAccordingToParameters($date, $command->dday);

        $extraData = $this->extraDataRepository->getForEventsAndNameWithOlderThanDate(
            $command->events,
            Type::PLANNING_MODIFIED,
            $date
        );

        foreach ($extraData as $extraDatum) {
            $this->notifyUserOfChangedVersionCommandHandler->handle(new NotifyUserOfChangedVersionCommand(
                $extraDatum->getEvent(),
                $extraDatum->getUser()
            ));
        }
    }

    private function modifyDateAccordingToParameters(\DateTime $dateTime, bool $isDDay): void
    {
        $dateTime->modify(
            sprintf(
                '-%d minutes',
                true === $isDDay
                    ? $this->agendaVersionDiffDDayNotificationTimeInMinutesParameters
                    : $this->agendaVersionDiffNotificationTimeInMinutesParameters
            )
        );
    }
}
