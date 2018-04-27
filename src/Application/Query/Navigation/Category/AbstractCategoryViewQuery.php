<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

abstract class AbstractCategoryViewQuery
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
    public $locale;

    /**
     * MemberSpaceViewQuery constructor.
     *
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     */
    public function __construct(Sheet $sheet, User $user, $locale)
    {
        $this->sheet  = $sheet;
        $this->user   = $user;
        $this->locale = $locale;
    }
}
