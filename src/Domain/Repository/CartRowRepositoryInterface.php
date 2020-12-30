<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;

interface CartRowRepositoryInterface
{
    /**
     * @param CartRow $cartRow
     */
    public function add(CartRow $cartRow);

    /**
     * @param CartRow $cartRow
     */
    public function set(CartRow $cartRow);

    /**
     * @param Sheet $sheet
     * @param array $cartRows
     */
    public function deleteWhereNotIn(Sheet $sheet, array $cartRows);

    /**
     * @param CartRow $cartRow
     */
    public function delete(CartRow $cartRow);

    /**
     * @param Sheet $sheet
     */
    public function deleteForSheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return CartRow[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Product $product
     *
     * @return CartRow[]
     */
    public function findByProduct($product);

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasProducts(Sheet $sheet);
}
