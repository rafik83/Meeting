<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Token;

use Proximum\Vimeet\Domain\Model\User;

abstract class AbstractTokenGenerator
{
    /**
     * @var \DateTimeImmutable
     */
    protected $expirateDate;

    /**
     * @param \DateTimeImmutable $dateTime
     */
    public function __construct(\DateTimeImmutable $dateTime)
    {
        $this->expirateDate = $dateTime->add($this->getLifetime());
    }

    /**
     * @param User $user
     *
     * @return string
     */
    protected function generateToken(User $user)
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
