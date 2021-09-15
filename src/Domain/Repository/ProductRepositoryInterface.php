<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;

interface ProductRepositoryInterface
{
    /**
     * @param Product $product
     */
    public function add(Product $product);

    /**
     * @param Product $product
     */
    public function remove(Product $product);

    /**
     * @param Event $event
     *
     * @return Product[]
     */
    public function findByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Product[]
     */
    public function findByEventOrderedByProductTypeAndProductName(Event $event): array;

    /**
     * @param Event $event
     *
     * @return Product[]
     */
    public function findOptionsByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return array ordered by product type, then by product name
     */
    public function countByEvent(Event $event);

    /**
     * @param Event $event
     * @param array $types
     *
     * @return Product[]
     */
    public function findByEventAndTypes(Event $event, array $types);

    /**
     * @param Product $product
     */
    public function update(Product $product);

    /**
     * @param array $productIds
     *
     * @return Product[]
     */
    public function findProductByIds(array $productIds);

    /**
     * @param int $productId
     *
     * @return null|Product
     */
    public function findById($productId);

    /**
     * @param Event $event
     *
     * @return Product[]
     */
    public function findRemovableProductsForEvent(Event $event): array;

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function isProductRemovable(Product $product): bool;

    /**
     * @param Event $event
     *
     * @return Product[]
     */
    public function findParticipantAndAttributableByEvent($event): array;

    /**
     * @param Event $event
     *
     * @return Product[]
     */
    public function findProductsBoughtByEvent(Event $event): array;
}
