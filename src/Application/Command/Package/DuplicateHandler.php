<?php

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Package\Duplicator;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class DuplicateHandler
{
    /** @var PackageRepositoryInterface */
    private $packageRepository;

    /** @var Duplicator */
    private $duplicator;

    /**
     * @param PackageRepositoryInterface $packageRepository
     * @param Duplicator                 $duplicator
     */
    public function __construct(PackageRepositoryInterface $packageRepository, Duplicator $duplicator)
    {
        $this->packageRepository = $packageRepository;
        $this->duplicator        = $duplicator;
    }

    /**
     * @param Duplicate $duplicate
     */
    public function handle(Duplicate $duplicate)
    {
        $package = $this->duplicator->duplicatePackage($duplicate->package, $duplicate->title);

        $this->packageRepository->add($package);
    }
}
