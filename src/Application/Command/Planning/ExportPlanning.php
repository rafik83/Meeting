<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planning;

use Proximum\Vimeet\Domain\Model\Event;

class ExportPlanning
{
    /**
     * @var array
     */
    public $typeIds;

    /**
     * @var string
     */
    public $orderBy;

    /**
     * @param array  $typeIds
     * @param string $orderBy
     */
    public function __construct(array $typeIds, $orderBy)
    {
        $this->typeIds = $typeIds;
        $this->orderBy = $orderBy;
    }
}
