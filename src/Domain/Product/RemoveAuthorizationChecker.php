<?php

namespace Proximum\Vimeet\Domain\Product;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class RemoveAuthorizationChecker
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var array index by event id */
    private $productsRemovable = [];

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param Event $event
     */
    public function preloadForEvent(Event $event): void
    {
        $this->productsRemovable[$event->getId()] = $this->productRepository->findRemovableProductsForEvent($event);
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function canBeRemoved(Product $product): bool
    {
        if (isset($this->productsRemovable[$product->getEvent()->getId()])) {
            return isset($this->productsRemovable[$product->getEvent()->getId()][$product->getId()]);
        }

        return $this->productRepository->isProductRemovable($product);
    }
}
