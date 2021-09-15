<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version;

use Proximum\Vimeet\Application\Exception\User\Agenda\Version\UserPhoneNotAvailableException;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffChecker;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;

class NotifyHandler
{
    /** @var \DateTimeInterface */
    public $dateTime;

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /** @var Generator */
    private $generator;

    /** @var DiffChecker */
    private $diffChecker;

    /** @var SendNotificationHandler */
    private $sendNotificationHandler;

    /**
     * @param SendNotificationHandler    $sendNotificationHandler
     * @param VersionRepositoryInterface $versionRepository
     * @param Generator                  $generator
     * @param DiffChecker                $diffChecker
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        SendNotificationHandler $sendNotificationHandler,
        VersionRepositoryInterface $versionRepository,
        Generator $generator,
        DiffChecker $diffChecker,
        \DateTimeInterface $dateTime
    ) {
        $this->versionRepository = $versionRepository;
        $this->generator = $generator;
        $this->dateTime = $dateTime;
        $this->diffChecker = $diffChecker;
        $this->sendNotificationHandler = $sendNotificationHandler;
    }

    /**
     * @param Notify $notify
     *
     * @throws UserPhoneNotAvailableException
     */
    public function handle(Notify $notify)
    {
        $oldVersion = $this->versionRepository->getLastVersionByEventAndUser($notify->event, $notify->user);

        if (null === $oldVersion) {
            $oldVersion = new Version($notify->event, $notify->user, [], $this->dateTime);
        }

        $diff = $this->generator->generate($notify->event, $notify->user);

        if ($this->diffChecker->hasDiff($oldVersion, $diff)) {
            $this->sendNotificationHandler->handle(
                new SendNotification($notify->event, $notify->sheet, $notify->user, $oldVersion, $diff)
            );

            $version = new Version($notify->event, $notify->user, $diff, $this->dateTime);
            $this->versionRepository->add($version);
        }
    }
}
