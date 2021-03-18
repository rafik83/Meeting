<?php

namespace Proximum\Vimeet\Domain\Repository\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Model\Product;

interface RowRepositoryInterface
{
    /**
     * @param Row $row
     */
    public function set(Row $row);

    /**
     * @param Row $row
     */
    public function remove(Row $row);

    /**
     * @param Product $product
     *
     * @return Order\Row[]
     */
    public function findByProduct(Product $product);

    /**
     * @param Product $product
     *
     * @return int
     */
    public function boughtByProduct(Product $product): int;
}
