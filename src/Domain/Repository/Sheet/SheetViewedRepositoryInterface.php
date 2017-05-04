<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Model\User;

interface SheetViewedRepositoryInterface
{
    /** @param SheetViewed $sheetViewed */
    public function add(SheetViewed $sheetViewed);

    /**
     * @param User  $user
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function isSheetAlreadySeenByUser(User $user, Sheet $sheet);
}
