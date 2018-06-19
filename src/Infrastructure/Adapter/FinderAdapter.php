<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\FinderAdapterInterface;
use Symfony\Component\Finder\Finder;

class FinderAdapter implements FinderAdapterInterface
{
    /** @var Finder */
    private $finder;

    public function __construct()
    {
        $this->finder = new Finder();
    }

    public function filesIn(string $path): Finder
    {
        return $this->finder->files()->in($path);
    }
}
