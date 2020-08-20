<?php

namespace Proximum\Vimeet\Domain\Happening\Webinar;

use DateTime;
use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Happening;

class IsRecordingAllowed
{
    /** @var DateTimeInterface */
    private $dateTime;

    public function __construct(DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function isSatisfiedBy(Happening $happening): bool
    {
        if (!$happening->isWebinarRecorded()) {
            return false;
        }

        $webinarEnd = $happening->getEnd();
        $webinarEndPlus30Minutes = new DateTime();
        $webinarEndPlus30Minutes->setTimestamp($webinarEnd->getTimestamp());
        $webinarEndPlus30Minutes->modify('+30 minutes');

        return $webinarEndPlus30Minutes > $this->dateTime;
    }
}
