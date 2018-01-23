<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Storage;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;

class Storage implements StorageInterface
{
    /**
     * @var array
     */
    private $storage;

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->storage[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return isset($this->storage[$name]) ? $this->storage[$name] : null;
    }
}
