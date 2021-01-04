<?php

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;

class PackageProductsNeededByHappening
{
    /**
     * @param Package   $package
     * @param Happening $happening
     *
     * @return Product[]
     */
    public function get(Package $package, Happening $happening): array
    {
        if (!$package->isPassable()) {
            return [];
        }

        if (!$happening->hasProducts()) {
            return [];
        }

        $happeningProductIndexedIds = [];

        foreach ($happening->getProducts() as $product) {
            $happeningProductIndexedIds[$product->getId()] = true;
        }

        $productsInPackageNeededByHappening = [];

        foreach ($package->getOptions() as $option) {
            if (isset($happeningProductIndexedIds[$option->getId()])) {
                $productsInPackageNeededByHappening[] = $option;
            }
        }

        return $productsInPackageNeededByHappening;
    }
}
