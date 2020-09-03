<?php

namespace Proximum\Vimeet\Domain\Happening\Webinar\Broadcast;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Time\DaysHelper;

class CanWebinarBeBroadcast
{
    /** @var DateTimeInterface */
    private $datetime;

    public function __construct(DateTimeInterface $dateTime)
    {
        $this->datetime = $dateTime;
    }

    public function __invoke(Happening $happening): bool
    {
        if (!$happening->isWebinar()) {
            return false;
        }

        if (!$happening->allowWebinarOnHLS()) {
            return false;
        }

        if (empty($happening->getWebinarSessionId())) {
            return false;
        }

        // Broadcast allowed between 20 minutes before and 30 minutes after the webinar.
        $begin = DaysHelper::cloneDateTime($happening->getBegin());
        $begin->modify('-20 minutes');

        $end = DaysHelper::cloneDateTime($happening->getEnd());
        $end->modify('+30 minutes');

        return $begin < $this->datetime && $this->datetime > $end;
    }
}
