<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Time\DaysHelper;

class CanAccessToWebinar
{
    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var bool */
    private $hasSecurity;

    /** @var bool */
    private $isVideoConferenceEnabled;

    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        \DateTimeInterface $dateTime,
        bool $hasSecurity,
        bool $isVideoConferenceEnabled
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->dateTime = $dateTime;
        $this->hasSecurity = $hasSecurity;
        $this->isVideoConferenceEnabled = $isVideoConferenceEnabled;
    }

    public function isSatisfiableBy(Happening $happening, User $user): bool
    {
        if (!$this->isVideoConferenceEnabled || !$happening->isWebinar()) {
            return false;
        }

        $start = DaysHelper::cloneDateTime($happening->getBegin())->modify('-5 min');
        $end = DaysHelper::cloneDateTime($happening->getEnd())->modify('+15 min');

        if ($this->hasSecurity && ($this->dateTime < $start || $this->dateTime > $end)) {
            return false;
        }

        if ($happening->hasSpeaker($user)) {
            return true;
        }

        return $this->happeningParticipationRepository
                ->findByHappeningAndUser($happening, $user) instanceof HappeningParticipation;
    }
}
