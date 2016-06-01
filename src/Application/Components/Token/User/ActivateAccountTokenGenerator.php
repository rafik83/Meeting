<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Token\User;

use Proximum\Vimeet\Application\Components\Token\AbstractTokenGenerator;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\User\ActivateAccountTokenRepositoryInterface;

class ActivateAccountTokenGenerator extends AbstractTokenGenerator
{
    /**
     * @var ActivateAccountTokenRepositoryInterface
     */
    private $respository;

    /**
     * ActivateAccountTokenGenerator constructor.
     *
     * @param ActivateAccountTokenRepositoryInterface $respository
     * @param \DateTimeImmutable                      $dateTime
     */
    public function __construct(ActivateAccountTokenRepositoryInterface $respository, \DateTimeImmutable $dateTime)
    {
        parent::__construct($dateTime);

        $this->respository = $respository;
    }

    /**
     * Delete all user token and generate a new one
     *
     * @param User  $user
     * @param Sheet $sheet
     *
     * @return ActivateAccountToken
     */
    public function generate(User $user, Sheet $sheet)
    {
        $token = new ActivateAccountToken($user, $this->generateToken($user), $sheet, $this->expirateDate);

        $this->respository->deleteAllForUser($user);
        $this->respository->create($token);

        return $token;
    }
}
