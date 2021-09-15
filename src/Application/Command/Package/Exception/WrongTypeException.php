<?php

namespace Proximum\Vimeet\Application\Command\Package\Exception;

use Proximum\Vimeet\Domain\Model\Product;

class WrongTypeException extends \Exception
{
    /**
     * WrongTypeException constructor.
     *
     * @param Product         $product
     * @param int             $expectedType
     * @param \Exception|null $previous
     */
    public function __construct(Product $product, $expectedType, \Exception $previous = null)
    {
        $message = sprintf('Product of type "%s" expected, type "%s" given', $product->getType(), $expectedType);

        parent::__construct($message, 0, $previous);
    }
}
