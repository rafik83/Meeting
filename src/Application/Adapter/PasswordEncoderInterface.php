<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface PasswordEncoderInterface
{
    /**
     * Encode password.
     *
     * @param string $password
     * @param string $salt
     *
     * @return string
     */
    public function encode($password, $salt);
}
