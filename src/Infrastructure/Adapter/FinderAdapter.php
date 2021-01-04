<?php

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

    public function filesIn(string $path): array
    {
        $files = $this->finder->files()->in($path);

        return iterator_to_array($files, false);
    }
}
