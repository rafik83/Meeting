<?php

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
        if (!is_string($numero)) {
            return false;
        }

        // The stucture of the numero should contains strickly 2 hyphens
        if (2 !== substr_count($numero, '-')) {
            return false;
        }

        $numeroElements = explode('-', $numero);

        // Each element between the hyphen should contains numbers
        foreach ($numeroElements as $element) {
            if (empty($element) || !preg_match('/^[0-9]+$/', $element)) {
                return false;
            }
        }

        return true;
    }
}
