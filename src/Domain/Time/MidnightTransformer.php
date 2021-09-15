<?php

namespace Proximum\Vimeet\Domain\Time;

class MidnightTransformer
{
    public static function getDateAtMidnight(\DateTimeInterface $dateTime): \DateTimeInterface
    {
        return (new \DateTime())
            ->setTimestamp($dateTime->getTimestamp())
            ->setTime(0, 0, 0, 0);
    }
}
