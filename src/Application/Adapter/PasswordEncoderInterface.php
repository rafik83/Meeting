<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface PasswordEncoderInterface
{
    /**
     * Encode password.
     *
     * @param mixed  $user
     * @param string $password
     *
     * @return string
     */
    public function encode($user, $password);
}
