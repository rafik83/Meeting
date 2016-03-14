<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Token\Admin;

use Proximum\Vimeet\Application\Components\Token\AbstractTokenGenerator;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Proximum\Vimeet\Domain\Model\Admin;

class ActivateAccountTokenGenerator extends AbstractTokenGenerator
{
    /**
     * @param Admin $admin
     *
     * @return ActivateAccountToken
     */
    public function generate(Admin $admin)
    {
        return new ActivateAccountToken($admin, $this->generateToken($admin), $this->expirateDate);
    }

    /**
     * @return \DateInterval
     */
    protected function getLifetime()
    {
        return new \DateInterval('P14D');
    }
}
