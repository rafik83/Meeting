<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Filter;

class RoomingListFilter extends AbstractFilter
{
    function getName(): string
    {
        return 'rooming_list_filters';
    }
}
