<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Rule\Strategy;

class SetNullStrategy extends AbstractRecursiveStrategy
{
    /**
     * {@inheritdoc}
     */
    public function doApply(&$data, $key)
    {
        $data[$key] = null;
    }
}
