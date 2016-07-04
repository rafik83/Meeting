<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\PromotionCode;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Summary\PromotionCode;

class Add
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var PromotionCode
     */
    public $promotionCodeForm;

    /**
     * Add constructor.
     *
     * @param Sheet         $sheet
     * @param PromotionCode $promotionCodeForm
     */
    public function __construct(Sheet $sheet, PromotionCode $promotionCodeForm)
    {
        $this->sheet             = $sheet;
        $this->promotionCodeForm = $promotionCodeForm;
    }

}
