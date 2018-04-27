<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
