<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\View\Unavailability\CreateUnavailabilitiesResultsView;
use Proximum\Vimeet\Application\View\Unavailability\CreateUnavailabilitiesResultView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;

class CreateUnavailabilitiesHandler
{
    /** @var GetTimezoneHelper */
    private $getTimezoneHelper;

    /** @var DayRepositoryInterface */
    private $dayRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        GetTimezoneHelper $getTimezoneHelper,
        DayRepositoryInterface $dayRepository,
        CommandBusInterface $commandBus
    ) {
        $this->getTimezoneHelper = $getTimezoneHelper;
        $this->dayRepository = $dayRepository;
        $this->commandBus = $commandBus;
    }

    public function handle(CreateUnavailabilities $command): CreateUnavailabilitiesResultsView
    {
        $result = [];
        $timezone = $this->getTimezoneHelper->getTimezoneByEventAndParticipant(
            $command->event,
            $command->participant
        );

        foreach ($command->payload as $payload) {
            if (empty($payload['day'] || empty($payload['unavailabilities']))) {
                continue;
            }

            $day = $this->dayRepository->findByEventStartTimeAndEndTime(
                $command->event,
                $this->convertTimestampToDateTime($payload['day']['start']),
                $this->convertTimestampToDateTime($payload['day']['end'])
            );

            if (!$day instanceof Day) {
                continue;
            }

            foreach ($payload['unavailabilities'] as $unavailability) {
                $createCommand = new Create(
                    $command->event,
                    $command->sheet,
                    $command->user,
                    $command->locale,
                    $timezone
                );
                $createCommand->time = $unavailability;
                $createCommand->day = $day;

                try {
                    $this->commandBus->handle($createCommand);
                    $result[] = new CreateUnavailabilitiesResultView($day, true);
                } catch (\Exception $exception) {
                    $result[] = new CreateUnavailabilitiesResultView($day, false);
                }
            }
        }

        return new CreateUnavailabilitiesResultsView($result);
    }

    private function convertTimestampToDateTime(string $timestamp): \DateTimeInterface
    {
        return (new \DateTime())
            ->setTimestamp($timestamp)
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    }
}
