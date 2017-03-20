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
     * @param string $key
     * @param mixed  $data
     *
     * @return
     */
    public function set($key, $data);

    /**
     * @param string $key
     */
    public function remove($key);
}
