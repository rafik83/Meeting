<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface FileStorageInterface
{
    /**
     * Upload a file and return a string identifier
     *
     * @param mixed $file
     *
     * @return string
     */
    public function upload($file);

    /**
     * @param string $identifier
     *
     * @return FileStorageInterface
     */
    public function remove($identifier);

    /**
     * @param string      $identifier
     * @param string|null $name
     *
     * @return string|null
     */
    public function copyAndRename($identifier, $name = null);
}
