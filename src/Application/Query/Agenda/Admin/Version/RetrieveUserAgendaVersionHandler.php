<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Version;

use Proximum\Vimeet\Application\View\Agenda\Admin\Version\UserAgendaVersionDiffView;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffChecker;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;

class RetrieveUserAgendaVersionHandler
{
    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DiffChecker */
    private $diffChecker;

    /** @var Generator */
    private $generator;

    /** @var DiffVerbalizer */
    private $diffVerbalizer;

    /**
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     * @param VersionRepositoryInterface        $versionRepository
     * @param \DateTimeInterface                $dateTime
     * @param DiffChecker                       $diffChecker
     * @param Generator                         $generator
     * @param DiffVerbalizer                    $diffVerbalizer
     */
    public function __construct(
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        VersionRepositoryInterface $versionRepository,
        \DateTimeInterface $dateTime,
        DiffChecker $diffChecker,
        Generator $generator,
        DiffVerbalizer $diffVerbalizer
    ) {
        $this->userEventPhoneRepository = $userEventPhoneRepository;
        $this->versionRepository = $versionRepository;
        $this->dateTime = $dateTime;
        $this->diffChecker = $diffChecker;
        $this->generator = $generator;
        $this->diffVerbalizer = $diffVerbalizer;
    }

    /**
     * @param RetrieveUserAgendaVersion $query
     *
     * @return UserAgendaVersionDiffView
     */
    public function handle(RetrieveUserAgendaVersion $query)
    {
        $userEventPhone = $this->userEventPhoneRepository->findValidated($query->user, $query->event);

        if (null === $userEventPhone) {
            return new UserAgendaVersionDiffView(UserAgendaVersionDiffView::ANSWER_NO_PHONE);
        }

        $version = $this->versionRepository->getLastVersionByEventAndUser($query->event, $query->user);

        if (null === $version) {
            $version = new Version($query->event, $query->user, [], $this->dateTime);
        }

        $diff = $this->generator->generate($query->event, $query->user);

        if ($this->diffChecker->hasDiff($version, $diff)) {
            return new UserAgendaVersionDiffView(
                UserAgendaVersionDiffView::ANSWER_DIFF,
                $this->diffVerbalizer->verbalizeDiff($version, $diff, $query->user->getLocale())
            );
        }

        return new UserAgendaVersionDiffView(
            UserAgendaVersionDiffView::ANSWER_NO_DIFF
        );
    }
}
