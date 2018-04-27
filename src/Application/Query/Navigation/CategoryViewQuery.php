<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class CategoryViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $categoryType;

    /**
     * @var string
     */
    public $locale;

    /**
     * CategoryViewQuery constructor.
     *
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $categoryType
     * @param        $locale
     */
    public function __construct(Sheet $sheet, User $user, $categoryType, $locale)
    {
        $this->sheet        = $sheet;
        $this->categoryType = $categoryType;
        $this->user         = $user;
        $this->locale       = $locale;
    }
}
