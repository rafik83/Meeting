<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

class SelectOptions extends AbstractStep
{
    /**
     * @var array of product id => quantity
     */
    public $options;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param int $id
     *
     * @return int
     */
    public function __get($id)
    {
        return $this->options[$id];
    }

    /**
     * @param int $id
     * @param int $quantity
     */
    public function __set($id, $quantity)
    {
        $this->options[$id] = $quantity;
    }
}
