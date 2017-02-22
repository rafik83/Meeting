<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class Create
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $number;

    /**
     * @var float
     */
    public $total;

    /**
     * @var float
     */
    public $totalWithVat;

    /**
     * @var float
     */
    public $vatAmount;

    /**
     * @var \DateTime
     */
    public $createdAt;
}
