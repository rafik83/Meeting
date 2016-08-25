<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use DateTimeInterface;

class BatchCatalog
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var DateTimeInterface
     */
    public $date;

    /**
     * @var bool
     */
    public $state;

    /**
     * BatchCatalog constructor.
     *
     * @param array             $ids
     * @param DateTimeInterface $date
     * @param bool              $state
     */
    public function __construct(array $ids, DateTimeInterface $date, $state)
    {
        $this->ids   = $ids;
        $this->date  = $date;
        $this->state = $state;}
}
