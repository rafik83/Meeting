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

class Add
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $promotionCode;

    /**
     * Add constructor.
     *
     * @param Sheet  $sheet
     * @param string $promotionCode
     */
    public function __construct(Sheet $sheet, $promotionCode)
    {
        $this->sheet         = $sheet;
        $this->promotionCode = $promotionCode;
    }

}
