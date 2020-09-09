<?php

namespace Proximum\Vimeet\Application\Components\Token;

use DateInterval;
use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Time\DaysHelper;

abstract class AbstractTokenGenerator
{
    /**
     * @var DateTimeInterface
     */
    protected $expirateDate;

    /**
     * @param DateTimeInterface $dateTime
     */
    public function __construct(DateTimeInterface $dateTime)
    {
        $date = DaysHelper::cloneDateTime($dateTime);
        $date->add($this->getLifetime());

        $this->expirateDate = $date;
    }

    /**
     * @param AbstractUser $user
     *
     * @return string
     */
    protected function generateToken(AbstractUser $user): string
    {
        return sha1(uniqid() . $user->getId() . uniqid() . $this->expirateDate->format('c'));
    }

    protected function getLifetime()
    {
        return new DateInterval('P2D');
    }
}
