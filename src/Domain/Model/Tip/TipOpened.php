<?php

namespace Proximum\Vimeet\Domain\Model\Tip;

use DateInterval;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\DaysHelper;

class TipOpened
{
    /** @var User */
    private $user;

    /** @var Tip */
    private $tip;

    /** @var \DateTimeInterface */
    private $openedAt;

    /**
     * @param User               $user
     * @param Tip                $tip
     * @param \DateTimeInterface $openedAt
     */
    public function __construct(User $user, Tip $tip, \DateTimeInterface $openedAt)
    {
        $this->user = $user;
        $this->tip = $tip;
        $this->openedAt = $openedAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTip(): Tip
    {
        return $this->tip;
    }

    public function getOpenedAt(): \DateTimeInterface
    {
        return $this->openedAt;
    }

    public function updateOpenedAt(\DateTimeInterface $openedAt): void
    {
        $this->openedAt = $openedAt;
    }

    public function isOpenedForMoreThanTwoHours(\DateTimeInterface $now)
    {
        $expiresAt = DaysHelper::cloneDateTime($this->openedAt);
        $expiresAt->add(new DateInterval('PT2H'));

        return $expiresAt < $now;
    }
}
