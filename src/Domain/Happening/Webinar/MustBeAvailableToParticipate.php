<?php

namespace Proximum\Vimeet\Domain\Happening\Webinar;

use Proximum\Vimeet\Domain\Model\Happening;

class MustBeAvailableToParticipate
{
    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function isSatisfiedBy(Happening $happening): bool
    {
        if (!$happening->getEvent()->getConfiguration()->isVisio()) {
            return true;
        }

        if ($this->dateTime < $happening->getBegin() || $this->dateTime > $happening->getEnd()) {
            return true;
        }

        return false;
    }
}
