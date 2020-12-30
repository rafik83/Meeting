<?php

namespace Proximum\Vimeet\Domain\Event\Product;

use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Product\Duplicator as ProductDuplicator;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class Duplicator
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductDuplicator */
    private $productDuplicator;

    /**
     * @param ProductDuplicator          $productDuplicator
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductDuplicator $productDuplicator,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productDuplicator = $productDuplicator;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     *
     * @return DuplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage): DuplicatorDataStorage
    {
        $products = $this->productRepository->findByEvent($event->getDuplicatedFrom());

        $toProducts = $this->productDuplicator->duplicateProducts($event, $products);

        foreach ($toProducts as $product) {
            $this->productRepository->add($product);
        }

        $duplicatorDataStorage->products = $toProducts;

        return $duplicatorDataStorage;
    }
}
