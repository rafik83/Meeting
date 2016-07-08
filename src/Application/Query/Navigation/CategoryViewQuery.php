<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Domain\Model\Sheet;

class CategoryViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $categoryType;

    /**
     * CategoryViewQuery constructor.
     *
     * @param Sheet  $sheet
     * @param string $categoryType
     */
    public function __construct(Sheet $sheet, $categoryType)
    {
        $this->sheet        = $sheet;
        $this->categoryType = $categoryType;
    }
}
