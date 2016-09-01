<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

abstract class AbstractBatch
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @return string
     */
    public function getMessage()
    {
        return 'flash.admin.sheet_batch.';
    }
}
