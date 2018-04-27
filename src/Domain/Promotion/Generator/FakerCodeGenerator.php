<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Promotion\Generator;

use Faker\Provider\Base;
use Proximum\Vimeet\Domain\Model\Event;

class FakerCodeGenerator implements CodeGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(Event $event)
    {
        return strtoupper(Base::lexify('??????'));
    }
}
