<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Token;

use DateTime;
use DateTimeImmutable;
use Proximum\Vimeet\Domain\Model\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Model\User;

class ForgottenPasswordTokenGenerator
{
    /**
     * @var DateTime
     */
    private $expirateDate;

    /**
     * @param DateTimeImmutable $dateTime
     */
    public function __construct(DateTimeImmutable $dateTime)
    {
        $this->expirateDate = $dateTime->add(new \DateInterval('P1D'));
    }

    /**
     * @param User $user
     *
     * @return ForgottenPasswordToken
     */
    public function generate(User $user)
    {
        return new ForgottenPasswordToken(
            $user,
            $this->generateToken($user),
            $this->expirateDate
        );
    }

    /**
     * @param User $user
     *
     * @return string
     */
    private function generateToken(User $user)
    {
        return sha1(uniqid() . $user->getId() . uniqid());
    }
}
