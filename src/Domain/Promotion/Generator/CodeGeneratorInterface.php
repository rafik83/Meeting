<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Promotion\Generator;

use Proximum\Vimeet\Domain\Model\Event;

interface CodeGeneratorInterface
{
    /**
     * @param Event $event
     *
     * @return string
     */
    public function generate(Event $event);
}
