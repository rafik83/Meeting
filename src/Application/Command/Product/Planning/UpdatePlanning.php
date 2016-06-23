<?php


namespace Proximum\Vimeet\Application\Command\Product\Planning;


use Proximum\Vimeet\Application\Command\Product\AbstractUpdate;
use Proximum\Vimeet\Domain\Model\Product;

class UpdatePlanning extends AbstractUpdate
{
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }
}