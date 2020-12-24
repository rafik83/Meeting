<?php

namespace Proximum\Vimeet\Domain\Product;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;

class Duplicator
{
    /** @var FileStorageInterface */
    private $fileStorage;

    /**
     * @param FileStorageInterface $fileStorage
     */
    public function __construct(FileStorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    /**
     * @param Event     $event        of the new product
     * @param Product[] $fromProducts
     *
     * @return Product[]
     */
    public function duplicateProducts(Event $event, array &$fromProducts): array
    {
        $toProducts = [];

        // Start to handle all the products expect plans as plans include other products
        foreach ($fromProducts as $fromProduct) {
            if (!$fromProduct->isPlan()) {
                $toProducts[$fromProduct->getId()] = $this->getToProduct($fromProduct, $event);
            }
        }

        foreach ($fromProducts as $fromPlan) {
            if ($fromPlan->isPlan()) {
                $toPlan = $this->getToProduct($fromPlan, $event);

                $this->handlePlan($toProducts, $fromPlan, $toPlan);
                $toProducts[$fromPlan->getId()] = $toPlan;
            }
        }

        return $toProducts;
    }

    /**
     * @param Product $fromProduct
     * @param Event   $event
     *
     * @return Product
     */
    private function getToProduct(Product $fromProduct, Event $event): Product
    {
        $image = $this->fileStorage->copyAndRename($fromProduct->getImage());

        $toProduct = new Product(
            $event,
            $fromProduct->getType(),
            $fromProduct->getName(),
            $image,
            $fromProduct->getUnitPrice(),
            $fromProduct->getVat(),
            $fromProduct->getRawQuantityMax(),
            $fromProduct->getAvailabilityCurrent(),
            $fromProduct->getAvailabilityMax(),
            $fromProduct->isUpdatable(),
            $fromProduct->getDeletableUntil(),
            $fromProduct->isSubjectedToValidation(),
            $fromProduct->getBuyableUntil()
        );

        $locales = $event->getLocales();
        foreach ($locales as $locale) {
            $toProduct->translate(
                $locale,
                $fromProduct->getTitle($locale),
                $fromProduct->getHeading($locale),
                $fromProduct->getDescription($locale),
                $fromProduct->getAddon($locale),
                $fromProduct->getSubjectedToValidationHelp($locale)
            );
        }

        $fromFeatures = $fromProduct->getFeatures();
        foreach ($fromFeatures as $fromFeature) {
            $toFeature = new Product\Feature($toProduct);

            foreach ($locales as $locale) {
                $toFeature->translate($locale, $fromFeature->getTitle($locale), $fromFeature->getDescription($locale));
            }
            $toProduct->addFeature($toFeature);
        }

        return $toProduct;
    }

    /**
     * @param array   $toProducts
     * @param Product $fromPlan
     * @param Product $toPlan
     */
    private function handlePlan(array &$toProducts, Product $fromPlan, Product $toPlan): void
    {
        foreach ($fromPlan->getIncludedProducts() as $includedProduct) {
            $toPlan->includeProduct(
                $toProducts[$includedProduct->getIncluded()->getId()],
                $includedProduct->getQuantity()
            );
        }
    }
}
