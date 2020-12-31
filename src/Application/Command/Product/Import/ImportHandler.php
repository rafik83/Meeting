<?php

namespace Proximum\Vimeet\Application\Command\Product\Import;

use Proximum\Vimeet\Domain\Package\Duplicator as PackageDuplicator;
use Proximum\Vimeet\Domain\Product\Duplicator as ProductDuplicator;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ImportHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var PackageRepositoryInterface */
    private $packageRepository;

    /** @var ProductDuplicator */
    private $productDuplicator;

    /** @var PackageDuplicator */
    private $packageDuplicator;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param PackageRepositoryInterface $packageRepository
     * @param ProductDuplicator          $productDuplicator
     * @param PackageDuplicator          $packageDuplicator
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        PackageRepositoryInterface $packageRepository,
        ProductDuplicator $productDuplicator,
        PackageDuplicator $packageDuplicator
    ) {
        $this->productRepository = $productRepository;
        $this->packageRepository = $packageRepository;
        $this->productDuplicator = $productDuplicator;
        $this->packageDuplicator = $packageDuplicator;
    }

    /**
     * @param Import $import
     */
    public function handle(Import $import)
    {
        // Retrieve products and package of the target event
        $fromProducts = $this->productRepository->findByEvent($import->event);
        $fromPackages = $this->packageRepository->findByEvent($import->event);

        $toProducts = $this->productDuplicator->duplicateProducts($import->toEvent, $fromProducts);

        foreach ($toProducts as $toProduct) {
            $this->productRepository->add($toProduct);
        }

        foreach ($fromPackages as $fromPackage) {
            $toPackage = $this->packageDuplicator->duplicatePackageWithCorrespondingProducts(
                $import->toEvent,
                $fromPackage,
                $toProducts
            );

            $this->packageRepository->add($toPackage);
        }
    }
}
