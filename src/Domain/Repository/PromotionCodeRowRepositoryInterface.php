<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;

interface PromotionCodeRowRepositoryInterface
{
    /**
     * @param PromotionCodeRow $promotionCodeRow
     */
    public function add(PromotionCodeRow $promotionCodeRow);

    /**
     * @param PromotionCodeRow $promotionCodeRow
     */
    public function set(PromotionCodeRow $promotionCodeRow);

    /**
     * @param Sheet $sheet
     *
     * @return PromotionCodeRow[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param PromotionCodeRow $promotionCodeRow
     */
    public function delete(PromotionCodeRow $promotionCodeRow);

    /**
     * @param Sheet $sheet
     */
    public function deleteForSheet(Sheet $sheet);
}
