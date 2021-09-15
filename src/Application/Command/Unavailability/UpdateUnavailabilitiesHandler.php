<?php

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsWithUnavailabilityException;
use Proximum\Vimeet\Application\Exception\Unavailability\UnavailabilityException;
use Proximum\Vimeet\Application\View\Unavailability\CreateUnavailabilitiesResultView;
use Proximum\Vimeet\Application\View\Unavailability\CreateUnavailabilitiesResultsView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Psr\Log\LoggerInterface;

class UpdateUnavailabilitiesHandler
{
    private GetTimezoneHelper $getTimezoneHelper;
    private CommandBusInterface $commandBus;
    private CreateHandler $createHandler;
    private ?LoggerInterface $logger;

    public function __construct(
        GetTimezoneHelper $getTimezoneHelper,
        CommandBusInterface $commandBus,
        CreateHandler $createHandler,
        LoggerInterface $logger = null
    ) {
        $this->getTimezoneHelper = $getTimezoneHelper;
        $this->commandBus = $commandBus;
        $this->createHandler = $createHandler;
        $this->logger = $logger;
    }

    public function handle(UpdateUnavailabilities $command): CreateUnavailabilitiesResultsView
    {
        // Remove old unavailabilities
        $this->commandBus->handle(new RemoveUserUnavailabilities($command->user, $command->event, $command->sheet));

        $result = [];
        $timezone = $this->getTimezoneHelper->getTimezoneByEventAndParticipant(
            $command->event,
            $command->participant
        );

        $days = $command->event->getDays();

        foreach ($command->payload as $payload) {
            if (empty($payload['day'] || empty($payload['unavailabilities']))) {
                continue;
            }

            foreach ($payload['unavailabilities'] as $unavailability) {
                [
                    $normalizedBeginDatetime,
                    $normalizedEndDatetime,
                ] = $this->getNormalizedBeginEndUnavailabilityDatetimes(
                    $payload['day'],
                    $timezone,
                    $unavailability
                );

                $day = $this->guessDay($days, $normalizedBeginDatetime);

                if (!$day instanceof Day) {
                    continue;
                }

                $normalizedUnavailabilites = [
                    'begin' => [
                        'hour'   => $normalizedBeginDatetime->format('H'),
                        'minute' => $normalizedBeginDatetime->format('i'),
                    ],
                    'end'   => [
                        'hour'   => $normalizedEndDatetime->format('H'),
                        'minute' => $normalizedEndDatetime->format('i'),
                    ],
                ];

                $createCommand = new Create(
                    $command->event,
                    $command->sheet,
                    $command->user,
                    $command->locale,
                    $normalizedBeginDatetime->getTimezone()->getName()
                );
                $createCommand->time = $normalizedUnavailabilites;
                $createCommand->day = $day;

                try {
                    $this->createHandler->handle($createCommand);
                    $result[] = new CreateUnavailabilitiesResultView($day, true);
                } catch (ParticipantsWithUnavailabilityException $exception) {
                    $result[] = new CreateUnavailabilitiesResultView($day, true);
                } catch (UnavailabilityException $exception) {
                    $result[] = new CreateUnavailabilitiesResultView($day, false);
                    if ($this->logger) {
                        $this->logger->warning(sprintf('Error when creating unavailability [%s] %s', get_class($exception), $exception->getMessage()));
                    }
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

    /**
     * @param array  $dayPayload
     * @param string $timezone
     * @param array  $unavailability
     *
     * @return \DateTimeInterface[]
     */
    private function getNormalizedBeginEndUnavailabilityDatetimes($dayPayload, string $timezone, $unavailability): array
    {
        /** @var \DateTime $beginDatetime */
        $beginDatetime = $this->convertTimestampToDateTime($dayPayload['start']);
        $beginDatetime->setTimezone(new \DateTimeZone($timezone));
        $beginDatetime->setTime($unavailability['begin']['hour'], $unavailability['begin']['minute']);

        $endDatetime = clone $beginDatetime;
        $endDatetime->setTime($unavailability['end']['hour'], $unavailability['end']['minute']);

        $beginDatetime
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $endDatetime
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return [$beginDatetime, $endDatetime];
    }

    /**
     * @param array              $days
     * @param \DateTimeInterface $beginDatetime
     *
     * @return Day|null
     */
    private function guessDay(array $days, \DateTimeInterface $beginDatetime): ?Day
    {
        $chosenDay = null;
        foreach ($days as $day) {
            if ($day->getStartTime()->getTimestamp() <= $beginDatetime->getTimestamp()
                && $day->getEndTime()->getTimestamp() >= $beginDatetime->getTimestamp()
            ) {
                $chosenDay = $day;
            }
        }

        return $chosenDay;
    }
}
