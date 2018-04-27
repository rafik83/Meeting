<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Phone;

class PhoneSanitizer
{
    /**
     * Remove all not 0-9 chars and return with "+" prefix
     *
     * @param string $phone
     *
     * @return string
     */
    public function handle($phone)
    {
        return '+' . preg_replace('/[^0-9]/', '', $phone);
    }
}
