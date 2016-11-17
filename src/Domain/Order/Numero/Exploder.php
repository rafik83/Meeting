<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Order\Numero;

use Proximum\Vimeet\Domain\Exception\Order\Numero\CanNotExplodeNotValidNumeroOrderException;

class Exploder
{
    /**
     * This method takes a numero in input
     * And it gives in output an array of eventId, sheetId and orderId
     * The elements that compose the numero
     *
     * @param string $numero
     *
     * @return array
     *
     * @throws CanNotExplodeNotValidNumeroOrderException
     */
    public static function explode($numero)
    {
        if (Validator::isValid($numero)) {
            $numeroElements = array_map('intval', explode('-', $numero));

            return $numeroElements;
        }

        throw new CanNotExplodeNotValidNumeroOrderException();
    }
}
