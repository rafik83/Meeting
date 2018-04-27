<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SheetDisabledException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'login.error.sheetDisabled';
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageData()
    {
        return ['login.error.sheetDisabled'];
    }
}
