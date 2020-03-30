<?php

namespace Proximum\Vimeet\Application\Components\Happening\Webinar;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Time\DaysHelper;

class CanAccessToWebinar
{
    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var bool */
    private $hasSecurity;

    /** @var bool */
    private $isVideoConferenceEnabled;

    public function __construct(\DateTimeInterface $dateTime, bool $hasSecurity, bool $isVideoConferenceEnabled)
    {
        $this->dateTime = $dateTime;
        $this->hasSecurity = $hasSecurity;
        $this->isVideoConferenceEnabled = $isVideoConferenceEnabled;
    }

    public function isSatisfiableBy(Happening $happening): bool
    {
        if (!$this->isVideoConferenceEnabled || !$happening->isWebinar()) {
            return false;
        }

        if (!$this->hasSecurity) {
            return true;
        }

        $start = DaysHelper::cloneDateTime($happening->getBegin())->modify('-5 min');
        $end = DaysHelper::cloneDateTime($happening->getEnd())->modify('+15 min');

        return $this->dateTime >= $start && $this->dateTime <= $end;
    }
}
