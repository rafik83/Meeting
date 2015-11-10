<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart\Carts;

interface LibCartInterface
{
    /**
     * @param array $template
     * @param array $dataValue
     * @param $locale
     *
     * @return array
     */
    public function prepare(array $template, array $dataValue, $locale);
}
