<?php

namespace Proximum\Vimeet\Domain\Event\Package;

use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Package\Duplicator as PackageDuplicator;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class Duplicator
{
    /** @var PackageRepositoryInterface */
    private $packageRepository;

    /** @var PackageDuplicator */
    private $packageDuplicator;

    /**
     * @param PackageDuplicator          $packageDuplicator
     * @param PackageRepositoryInterface $packageRepository
     */
    public function __construct(PackageDuplicator $packageDuplicator, PackageRepositoryInterface $packageRepository)
    {
        $this->packageRepository = $packageRepository;
        $this->packageDuplicator = $packageDuplicator;
    }

    /**
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     *
     * @return DuplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage): DuplicatorDataStorage
    {
        $packages = $this->packageRepository->findByEvent($event->getDuplicatedFrom());

        foreach ($packages as $package) {
            $newPackage = $this->packageDuplicator->duplicatePackageWithCorrespondingProducts(
                $event,
                $package,
                $duplicatorDataStorage->products
            );

            $duplicatorDataStorage->packageTemplates[$package->getId()] = $newPackage;
            $this->packageRepository->add($newPackage);
        }

        return $duplicatorDataStorage;
    }
}
