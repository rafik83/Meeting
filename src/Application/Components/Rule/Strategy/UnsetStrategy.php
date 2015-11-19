<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Rule\Strategy;

class UnsetStrategy extends AbstractRecursiveStrategy
{
    /**
     * {@inheritdoc}
     */
    function doApply(&$data, $key)
    {
        unset($data[$key]);
    }
}
