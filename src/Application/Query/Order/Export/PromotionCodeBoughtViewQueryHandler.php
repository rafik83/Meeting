<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Application\View\Order\Export\PromotionCodeBoughtView;

class PromotionCodeBoughtViewQueryHandler
{
    /**
     * @param PromotionCodeBoughtViewQuery $query
     *
     * @return PromotionCodeBoughtView
     */
    public function handle(PromotionCodeBoughtViewQuery $query)
    {
        return new PromotionCodeBoughtView(
            $query->promotionCode->getPromotionCode()->getId(),
            1,
            $query->promotionCode->getPrice()
        );
    }
}
