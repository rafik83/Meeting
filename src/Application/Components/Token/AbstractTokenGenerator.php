<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Token;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\User;

class AbstractTokenGenerator
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
        $this->expirateDate = $dateTime->add(new \DateInterval('P2D'));
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
}
