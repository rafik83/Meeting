<?php

namespace Proximum\Vimeet\Application\Command\Product\Participant;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Product\ProductUpdatedEvent;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class UpdateParticipantHandler
{
    /**
     * @var DelayedEventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var UpdatePriceResolver
     */
    private $updatePriceResolver;

    public function __construct(
        DelayedEventDispatcherInterface $eventDispatcher,
        ProductRepositoryInterface $productRepository,
        UpdatePriceResolver $updatePriceResolver
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->productRepository = $productRepository;
        $this->updatePriceResolver = $updatePriceResolver;
    }

    /**
     * @param UpdateParticipant $updateParticipant
     */
    public function handle(UpdateParticipant $updateParticipant)
    {
        $canUpdatePriceAndVat = $this->updatePriceResolver->resolve($updateParticipant->product);
        $product = $updateParticipant->product->updateParticipant(
            $updateParticipant->name,
            $updateParticipant->quantityMax,
            $canUpdatePriceAndVat ? $updateParticipant->unitPrice : $updateParticipant->product->getUnitPrice(),
            $canUpdatePriceAndVat ? $updateParticipant->vat : $updateParticipant->product->getVat()
        );

        foreach ($updateParticipant->translations as $locale => $translation) {
            $product->translate($locale, $translation['title'], null, $translation['description'], null, null);
        }

        $previousAvailabilityTimeRanges = $product->getAvailabilityTimeRanges();

        $product->setAvailabilityTimeRanges($updateParticipant->availabilityTimeRanges);

        $this->productRepository->update($product);

        $this->eventDispatcher->dispatch(
            Events::PRODUCT_UPDATED,
            new ProductUpdatedEvent($product, $previousAvailabilityTimeRanges)
        );
    }
}
