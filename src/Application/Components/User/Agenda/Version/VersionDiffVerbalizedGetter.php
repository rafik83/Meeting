<?php

namespace Proximum\Vimeet\Application\Components\User\Agenda\Version;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffChecker;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;

class VersionDiffVerbalizedGetter
{
    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /** @var Generator */
    private $generator;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DiffChecker */
    private $diffChecker;

    /** @var DiffVerbalizer */
    private $diffVerbalizer;

    public function __construct(
        VersionRepositoryInterface $versionRepository,
        Generator $generator,
        DiffChecker $diffChecker,
        DiffVerbalizer $diffVerbalizer,
        \DateTimeInterface $dateTime
    ) {
        $this->versionRepository = $versionRepository;
        $this->generator = $generator;
        $this->dateTime = $dateTime;
        $this->diffChecker = $diffChecker;
        $this->diffVerbalizer = $diffVerbalizer;
    }

    public function getVerbalizedDiff(Event $event, User $user): string
    {
        $oldVersion = $this->versionRepository->getLastVersionByEventAndUser($event, $user);

        if (null === $oldVersion) {
            $oldVersion = new User\Agenda\Version($event, $user, [], $this->dateTime);
        }

        $diff = $this->generator->generate($event, $user);

        if (!$this->diffChecker->hasDiff($oldVersion, $diff)) {
            return '';
        }

        return $this->diffVerbalizer->verbalizeDiff(
            $oldVersion,
            $diff,
            $user->getLocale()
        );
    }
}
