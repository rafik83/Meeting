<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Nomenclature\Id;

class UniquIdGenerator implements IdGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        return uniqid('u');
    }
}
