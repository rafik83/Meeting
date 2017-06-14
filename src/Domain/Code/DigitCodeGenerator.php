<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Code;

class DigitCodeGenerator
{
    /**
     * @param int $length Length of the code
     *
     * @return string
     */
    public function generateCode($length)
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('The length can not be smaller than 1');
        }

        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= (string) mt_rand(0, 9);
        }

        $exclude = [];

        for ($digit = 0; $digit <= 9; $digit++) {
            $exclude[] = str_repeat((string) $digit, $length);
        }

        return in_array($code, $exclude) ? $this->generateCode($length) : $code;
    }
}
