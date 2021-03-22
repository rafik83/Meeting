<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification;

use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffChecker;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class NotifyUserOfChangedVersionCommandHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var SheetGuesser */
    private $sheetGuesser;

    /** @var MailNotificationCommandHandler */
    private $mailNotificationCommandHandler;

    /** @var SMSNotificationCommandHandler */
    private $SMSNotificationCommandHandler;

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var Generator */
    private $diffGenerator;

    /** @var DiffChecker */
    private $diffChecker;

    /** @var DiffVerbalizer */
    private $diffVerbalizer;

    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        SheetGuesser $sheetGuesser,
        VersionRepositoryInterface $versionRepository,
        Generator $diffGenerator,
        DiffChecker $diffChecker,
        DiffVerbalizer $diffVerbalizer,
        MailNotificationCommandHandler $mailNotificationCommandHandler,
        SMSNotificationCommandHandler $SMSNotificationCommandHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->sheetGuesser = $sheetGuesser;
        $this->mailNotificationCommandHandler = $mailNotificationCommandHandler;
        $this->SMSNotificationCommandHandler = $SMSNotificationCommandHandler;
        $this->versionRepository = $versionRepository;
        $this->dateTime = $dateTime;
        $this->diffGenerator = $diffGenerator;
        $this->diffChecker = $diffChecker;
        $this->diffVerbalizer = $diffVerbalizer;
    }

    public function handle(NotifyUserOfChangedVersionCommand $command): void
    {
        try {
            $sheet = $this->sheetGuesser->getUserSheet(
                $command->user,
                $command->event,
                $command->event->getAvailableLocale($command->user->getLocale())
            );
        } catch (SheetNotFoundException $exception) {
            $this->extraDataRepository->removeForUserAndEventAndName(
                $command->user,
                $command->event,
                Type::PLANNING_MODIFIED
            );

            return;
        }

        $oldVersion = $this->versionRepository->getLastVersionByEventAndUser(
            $command->event,
            $command->user
        );

        if (null === $oldVersion) {
            $oldVersion = new Version($command->event, $command->user, [], $this->dateTime);
        }

        $diff = $this->diffGenerator->generate($command->event, $command->user);

        if (!$this->diffChecker->hasDiff($oldVersion, $diff)) {
            $this->extraDataRepository->removeForUserAndEventAndName(
                $command->user,
                $command->event,
                Type::PLANNING_MODIFIED
            );

            return;
        }

        $verbalizedDiff = $this->diffVerbalizer->verbalizeDiff(
            $oldVersion,
            $diff,
            $sheet->getUserLocale($command->user)
        );

        $smsActivationDate = $command->event->getConfiguration()->getSmsActivationDate();

        if ($smsActivationDate !== null && $smsActivationDate < $this->dateTime) {
            $this->SMSNotificationCommandHandler->handle(new SMSNotificationCommand(
                $command->event,
                $command->user,
                $sheet,
                $verbalizedDiff
            ));
        }

        $this->mailNotificationCommandHandler->handle(new MailNotificationCommand(
            $command->event,
            $command->user,
            $sheet,
            $verbalizedDiff
        ));

        $this->extraDataRepository->removeForUserAndEventAndName(
            $command->user,
            $command->event,
            Type::PLANNING_MODIFIED
        );

        $version = new Version($command->event, $command->user, $diff, $this->dateTime);
        $this->versionRepository->add($version);
    }
}
