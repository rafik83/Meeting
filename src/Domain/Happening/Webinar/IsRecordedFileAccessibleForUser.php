<?php

namespace Proximum\Vimeet\Domain\Happening\Webinar;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class IsRecordedFileAccessibleForUser
{
    /** @var DateTimeInterface */
    private $dateTime;

    public function __construct(DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function isSatisfiedBy(Happening $happening, User $user): bool
    {
        if (!$happening->isWebinarRecorded()) {
            return false;
        }

        $endTime = $happening->getEnd();
        $endTime->modify('+7 days');

        if ($endTime < $this->dateTime) {
            return false;
        }

        $speakers = $happening->getSpeakers();
        $userIsSpeaker = false;
        foreach ($speakers as $speaker) {
            if ($user !== $speaker->getUser()) {
                continue;
            }

            $userIsSpeaker = true;
        }

        if (!$userIsSpeaker) {
            return false;
        }

        return $happening->hasWebinarRecordZipFileUrl();
    }
}
