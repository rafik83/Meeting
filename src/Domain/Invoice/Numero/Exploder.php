<?php

namespace Proximum\Vimeet\Domain\Invoice\Numero;

use Proximum\Vimeet\Domain\Exception\Invoice\CanNotExplodeNotValidNumeroInvoiceException;

class Exploder
{
    /**
     * This method takes a numero in input
     * And it gives in output a view composed of the prefix, the year and the increment
     * The elements that compose the numero
     *
     * @param string $numero
     *
     * @throws CanNotExplodeNotValidNumeroInvoiceException
     *
     * @return InvoiceNumeroView
     */
    public static function explode($numero)
    {
        $numeroElements = explode('-', $numero);

        if (!empty($numeroElements) && count($numeroElements) > 1) {
            $lastElement = end($numeroElements);

            if (false !== $lastElement && 0 !== intval($lastElement)) {
                $increment = $lastElement;

                // Remove last element of array
                array_pop($numeroElements);

                $newLastElement = end($numeroElements);

                if (false !== $newLastElement) {
                    $numberOfCharInString = mb_strlen($newLastElement);

                    // As prefix could be nullable
                    if ($numberOfCharInString >= 4) {
                        $year = mb_substr($newLastElement, -4);

                        // The year should be composed of 4 number
                        if (intval($year) > 1000) {
                            $numberOfCharToRemove = mb_strlen(sprintf(('%s-%s'), $year, $increment));

                            $prefix = mb_substr($numero, 0, mb_strlen($numero) - $numberOfCharToRemove);

                            return new InvoiceNumeroView($prefix, intval($year), intval($increment));
                        }
                    }
                }
            }
        }

        throw new CanNotExplodeNotValidNumeroInvoiceException($numero);
    }
}
