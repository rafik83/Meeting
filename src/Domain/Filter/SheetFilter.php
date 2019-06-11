<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Filter;

class SheetFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'sheet_filters';
    }
}
