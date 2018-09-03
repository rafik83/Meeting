<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet;

class PasswordGenerator
{
    public static function generate(int $length = 10): string
    {
        $password = '';
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?';

        for ($i = 0; $i <= $length; ++$i) {
            $password .= $characters[random_int(0, \strlen($characters) - 1)];
        }

        return $password;
    }
}
