<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\PromotionCode;

use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;

class Remove
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var PromotionCodeRow
     */
    public $promotionCodeRow;

    /**
     * Remove constructor.
     *
     * @param Sheet            $sheet
     * @param PromotionCodeRow $promotionCodeRow
     */
    public function __construct(Sheet $sheet, PromotionCodeRow $promotionCodeRow)
    {
        $this->sheet            = $sheet;
        $this->promotionCodeRow = $promotionCodeRow;
    }
}
