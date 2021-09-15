<?php

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
     * @throws CanNotExplodeNotValidNumeroOrderException
     *
     * @return OrderNumeroView
     */
    public static function explode($numero)
    {
        if (Validator::isValid($numero)) {
            $numeroElements = array_map('intval', explode('-', $numero));

            return new OrderNumeroView($numeroElements[0], $numeroElements[1], $numeroElements[2]);
        }

        throw new CanNotExplodeNotValidNumeroOrderException();
    }
}
