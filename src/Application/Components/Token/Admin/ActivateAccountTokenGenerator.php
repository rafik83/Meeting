<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Token\Admin;

use Proximum\Vimeet\Application\Components\Token\AbstractTokenGenerator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\Admin\ActivateAccountTokenRepositoryInterface;

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
     * @param \DateTimeInterface                      $dateTime
     */
    public function __construct(ActivateAccountTokenRepositoryInterface $respository, \DateTimeInterface $dateTime)
    {
        parent::__construct($dateTime);

        $this->respository = $respository;
    }

    /**
     * Delete all user token and generate a new one
     *
     * @param Admin $admin
     *
     * @return ActivateAccountToken
     */
    public function generate(Admin $admin)
    {
        $token = new ActivateAccountToken($admin, $this->generateToken($admin), $this->expirateDate);

        $this->respository->deleteAllForUser($admin);
        $this->respository->create($token);

        return $token;
    }

    /**
     * @return \DateInterval
     */
    protected function getLifetime()
    {
        return new \DateInterval('P14D');
    }
}
