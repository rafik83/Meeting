<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Rooming\ExportList;

class UserSheetView
{
    public function __construct(
        int $userId,
        ?string $gender,
        ?string $firstName,
        ?string $lastName,
        string $sheetIds,
        ?string $sheetTitles,
        ?string $typeTitles
    ) {
    }
}
