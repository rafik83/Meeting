<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Order\Numero;

class Validator
{
    /**
     * @param string $numero
     *
     * @return bool
     */
    public static function isValid($numero)
    {
        // The stucture of the numero should contains strickly 2 hyphens
        if (2 !== substr_count($numero, '-')) {
            return false;
        }

        $numeroElements = explode('-', $numero);

        // Each element between the hyphen should contains numbers
        foreach ($numeroElements as $element) {
            if (empty($element) && !preg_match("/^[0-9]+$/", $element)) {
                return false;
            }
        }

        return true;
    }
}
