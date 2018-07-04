<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface ZipArchiveAdapterInterface
{
    public function zipFiles(array $files, string $zipName, string $rootDir, ?string $password = null): void;
}
