<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version;

use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\MailNotificationCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\MailNotificationCommandHandler;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\SMSNotificationCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\SMSNotificationCommandHandler;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;

class SendNotificationHandler
{
    /** @var DiffVerbalizer */
    private $diffVerbalizer;

    /** @var MailNotificationCommandHandler */
    private $mailNotificationCommandHandler;

    /** @var SMSNotificationCommandHandler */
    private $SMSNotificationCommandHandler;

    /** @var PlannerJobRepositoryInterface */
    private $plannerJobRepository;

    public function __construct(
        DiffVerbalizer $diffVerbalizer,
        MailNotificationCommandHandler $mailNotificationCommandHandler,
        SMSNotificationCommandHandler $SMSNotificationCommandHandler,
        PlannerJobRepositoryInterface $plannerJobRepository
    ) {
        $this->diffVerbalizer = $diffVerbalizer;
        $this->mailNotificationCommandHandler = $mailNotificationCommandHandler;
        $this->SMSNotificationCommandHandler = $SMSNotificationCommandHandler;
        $this->plannerJobRepository = $plannerJobRepository;
    }

    /**
     * @param SendNotification $command
     */
    public function handle(SendNotification $command): void
    {

        $lastPlannerJob = $this->plannerJobRepository->findLastByEvent($command->event);

        if (null !== $lastPlannerJob && !$lastPlannerJob->isCompleted()) {
            return;
        }

        $verbalizedDiff = $this->diffVerbalizer->verbalizeDiff(
            $command->currentVersion,
            $command->diff,
            $command->user->getLocale()
        );

        $this->mailNotificationCommandHandler->handle(new MailNotificationCommand(
            $command->event,
            $command->user,
            $command->sheet,
            $verbalizedDiff
        ));

        $this->SMSNotificationCommandHandler->handle(new SMSNotificationCommand(
            $command->event,
            $command->user,
            $command->sheet,
            $verbalizedDiff
        ));
    }
}
