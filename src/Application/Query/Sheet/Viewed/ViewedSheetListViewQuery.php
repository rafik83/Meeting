<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Viewed;

use Proximum\Vimeet\Domain\Model\User;

class ViewedSheetListViewQuery
{
    /** @var User */
    public $user;

    /** @var int[] */
    public $sheetIds;

    /**
     * ViewedSheetListViewQuery constructor.
     *
     * @param User  $user
     * @param int[] $sheetIds
     */
    public function __construct(User $user, array $sheetIds)
    {
        $this->user     = $user;
        $this->sheetIds = $sheetIds;
    }
}
