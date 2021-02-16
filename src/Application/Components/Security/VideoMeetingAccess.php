<?php

namespace Proximum\Vimeet\Application\Components\Security;

use Proximum\Vimeet\Domain\Model\Meeting;

class VideoMeetingAccess
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var bool
     */
    private $hasSecurity;

    /**
     * @var bool
     */
    private $isVideoConferenceEnabled;

    /**
     * VideoMeetingAccess constructor.
     *
     * @param \DateTimeInterface $dateTime
     * @param bool               $hasSecurity
     * @param bool               $isVideoConferenceEnabled
     */
    public function __construct(\DateTimeInterface $dateTime, bool $hasSecurity, bool $isVideoConferenceEnabled)
    {
        $this->dateTime = $dateTime;
        $this->hasSecurity = $hasSecurity;
        $this->isVideoConferenceEnabled = $isVideoConferenceEnabled;
    }

    /**
     * @param Meeting $meeting
     *
     * @return bool
     */
    public function allowedToAccess(Meeting $meeting): bool
    {
        if (!$this->isVideoConferenceEnabled) {
            return false;
        }

        if (!$this->hasSecurity) {
            return true;
        }

        $start = (clone $meeting->getSlot()->getBegin())->modify('-15 min');
        $end = (clone $meeting->getSlot()->getEnd())->modify('+15 min');

        return $this->dateTime >= $start && $this->dateTime <= $end;
    }
}
