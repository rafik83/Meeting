<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Apply\Strategy;

interface StrategyInterface
{
    /**
     * @param array $data
     * @param array $rules
     *
     * @return array
     */
    public function apply(array $data, array $rules);
}
