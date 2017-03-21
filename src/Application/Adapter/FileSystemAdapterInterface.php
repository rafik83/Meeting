<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Symfony\Component\Filesystem\Exception\IOException;

interface FileSystemAdapterInterface
{
    /**
     * @param string $filename The file to be written to
     * @param string $content  The data to write into the file
     *
     * @throws IOException If the file cannot be written to.
     */
    public function dumpFile($filename, $content);
}
