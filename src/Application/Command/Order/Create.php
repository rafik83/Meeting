<?php

/*
<<<<<<< HEAD
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
=======
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
>>>>>>> master
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Model\Sheet;

class Create
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
<<<<<<< HEAD
     * Create constructor.
     *
=======
>>>>>>> master
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
