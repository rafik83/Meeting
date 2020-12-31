<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;
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

    /** @var PlannerJobRepositoryInterface */
    private $plannerJobRepository;

    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        int $agendaVersionDiffNotificationTimeInMinutesParameters,
        int $agendaVersionDiffDDayNotificationTimeInMinutesParameters,
        NotifyUserOfChangedVersionCommandHandler $notifyUserOfChangedVersionCommandHandler,
        \DateTimeInterface $dateTime,
        PlannerJobRepositoryInterface $plannerJobRepository
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->agendaVersionDiffNotificationTimeInMinutesParameters = $agendaVersionDiffNotificationTimeInMinutesParameters;
        $this->agendaVersionDiffDDayNotificationTimeInMinutesParameters = $agendaVersionDiffDDayNotificationTimeInMinutesParameters;
        $this->notifyUserOfChangedVersionCommandHandler = $notifyUserOfChangedVersionCommandHandler;
        $this->dateTime = $dateTime;
        $this->plannerJobRepository = $plannerJobRepository;
    }

    public function handle(RecurrentNotificationOfChangedInVersionCommand $command): void
    {
        $events = array_filter($command->events, function (Event $event) {
            return !$this->hasPendingPlannerJob($event);
        });

        if (empty($events)) {
            return;
        }

        $date = (new \DateTime())->setTimestamp($this->dateTime->getTimestamp());
        $this->modifyDateAccordingToParameters($date, $command->dday);

        $extraData = $this->extraDataRepository->getForEventsAndNameWithOlderThanDate(
            $events,
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

    private function hasPendingPlannerJob(Event $event): bool
    {
        $lastPlannerJob = $this->plannerJobRepository->findLastByEvent($event);

        return null !== $lastPlannerJob && !$lastPlannerJob->isCompleted();
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
