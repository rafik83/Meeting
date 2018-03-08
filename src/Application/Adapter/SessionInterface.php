<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface SessionInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Gets and clears flash from the stack.
     *
     * @param string $type
     * @param array  $default Default value if $type does not exist
     *
     * @return array
     */
    public function getFromFlashBag($type, array $default = []): array;

    /**
     * @param string $key
     * @param mixed  $data
     */
    public function set($key, $data);

    /**
     * @param string $key
     */
    public function remove($key);
}
