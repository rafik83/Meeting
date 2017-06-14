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
            $code .= mt_rand(0, 9);
        }

        $exclude = [
            str_repeat('0', $length),
            str_repeat('1', $length),
            str_repeat('2', $length),
            str_repeat('3', $length),
            str_repeat('4', $length),
            str_repeat('5', $length),
            str_repeat('6', $length),
            str_repeat('7', $length),
            str_repeat('8', $length),
            str_repeat('9', $length),
        ];

        return in_array($code, $exclude) ? $this->generateCode($length) : $code;
    }
}
