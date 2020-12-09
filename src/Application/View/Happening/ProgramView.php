<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening;

class ProgramView
{
    /**
     * @var DayView[]
     */
    public $days;

    /**
     * @var null|string
     */
    public $categoryTitle;

    /**
     * @var null|string
     */
    public $categoryPicto;

    /**
     * @param array $days
     * @param string|null categoryTitle
     * @param string|null $categoryPicto
     */
    public function __construct(
        array $days = [],
        $categoryTitle = null,
        $categoryPicto = null
    ) {
        $this->days          = $days;
        $this->categoryTitle = $categoryTitle;
        $this->categoryPicto = $categoryPicto;
    }

    /**
     * @return bool
     */
    public function hasCategory()
    {
        return null !== $this->categoryTitle;
    }

    /**
     * @return string
     */
    public function getCategoryTitle()
    {
        return null !== $this->categoryTitle ? $this->categoryTitle : '';
    }

    /**
     * @return string
     */
    public function getCategoryPicto()
    {
        return null !== $this->categoryPicto ? $this->categoryPicto : 'Conference';
    }

    /**
     * @return DayView|null
     */
    public function getFirstDay()
    {
        return reset($this->days);
    }
}
