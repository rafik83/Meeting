<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart\Carts;

use Proximum\Vimeet\Application\Components\Cart\CartRow;

interface LibCartInterface
{
    /**
     * @param array  $template
     * @param array  $dataValue
     * @param string $locale
     *
     * @return CartRow|null
     */
    public function prepare(array $template, array $dataValue, $locale);
}
