<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Symfony\Component\Finder\Finder;

interface ZipArchiveAdapterInterface
{
    public function zipFiles(Finder $files, string $zipName, string $rootDir, ?string $password = null): void;
}
