<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class PostBatchDuplicateSheets
{
    /** @var Sheet[] */
    public $sheets;

    /** @var Type */
    public $type;

    public function __construct(array $sheets, Type $type)
    {
        $this->sheets = $sheets;
        $this->type = $type;
    }
}
