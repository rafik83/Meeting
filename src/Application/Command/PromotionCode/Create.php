<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Model\Event;

class Create
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $title;
}
