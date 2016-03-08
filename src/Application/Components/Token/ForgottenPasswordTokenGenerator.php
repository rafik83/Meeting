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
use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken as UserForgottenPasswordToken;
use Proximum\Vimeet\Domain\Model\Admin\ForgottenPasswordToken as AdminForgottenPasswordToken;
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
     * @param AbstractUser $user
     *
     * @return UserForgottenPasswordToken|AdminForgottenPasswordToken|null
     */
    public function generate(AbstractUser $user)
    {
        if ($user instanceof User) {
            return new UserForgottenPasswordToken(
                $user,
                $this->generateToken($user),
                $this->expirateDate
            );
        } elseif ($user instanceof Admin) {
            return new AdminForgottenPasswordToken(
                $user,
                $this->generateToken($user),
                $this->expirateDate
            );
        }

        return null;
    }

    /**
     * @param AbstractUser $user
     *
     * @return string
     */
    private function generateToken(AbstractUser $user)
    {
        return sha1(uniqid() . $user->getId() . uniqid());
    }
}
