<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Viewed;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ViewedSheetListViewQuery
{
    /** @var User */
    public $user;

    /** @var Sheet[] */
    public $sheets;

    /**
     * ViewedSheetListViewQuery constructor.
     *
     * @param User    $user
     * @param Sheet[] $sheets
     */
    public function __construct(User $user, array $sheets)
    {
        $this->user   = $user;
        $this->sheets = $sheets;
    }
}
