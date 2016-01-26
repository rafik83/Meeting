<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class AddNegative
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var array
     */
    public $packageData;

    /**
     * @var DateTimeInterface
     */
    public $createdAt;

    /**
     * @param Sheet             $sheet
     * @param array             $packageData
     * @param DateTimeInterface $createdAt
     */
    public function __construct(
        Sheet $sheet,
        array $packageData,
        DateTimeInterface $createdAt
    ) {
        $this->sheet       = $sheet;
        $this->packageData = $packageData;
        $this->createdAt   = $createdAt;
    }
}
