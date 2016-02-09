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
use DateTimeImmutable;
use Proximum\Vimeet\Domain\Model\ActivateAccountToken;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ActivateAccountTokenGenerator
{
    /**
     * @var DateTimeInterface
     */
    private $expirateDate;

    /**
     * @param DateTimeImmutable $dateTime
     */
    public function __construct(DateTimeImmutable $dateTime)
    {
        $this->expirateDate = $dateTime->add(new \DateInterval('P2D'));
    }

    /**
     * @param User  $user
     * @param Sheet $sheet
     *
     * @return ActivateAccountToken
     */
    public function generate(User $user, Sheet $sheet)
    {
        return new ActivateAccountToken(
            $user,
            $this->generateToken($user),
            $sheet,
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
        return sha1(uniqid() . $user->getId() . uniqid() . $this->expirateDate->format('c'));
    }
}
