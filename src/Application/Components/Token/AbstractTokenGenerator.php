<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Token;

use Proximum\Vimeet\Domain\Model\AbstractUser;

abstract class AbstractTokenGenerator
{
    /**
     * @var \DateTimeInterface
     */
    protected $expirateDate;

    /**
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->expirateDate = $dateTime->add($this->getLifetime());
    }

    /**
     * @param AbstractUser $user
     *
     * @return string
     */
    protected function generateToken(AbstractUser $user)
    {
        return sha1(uniqid() . $user->getId() . uniqid() . $this->expirateDate->format('c'));
    }

    /**
     * @return \DateInterval
     */
    protected function getLifetime()
    {
        return new \DateInterval('P2D');
    }
}
