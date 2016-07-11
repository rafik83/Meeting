<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class PlanningViewQuery
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
     * HappeningViewQuery constructor.
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
