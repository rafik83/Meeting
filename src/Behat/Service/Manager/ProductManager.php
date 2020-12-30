<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductManager
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param Event  $event
     * @param string $title
     * @param float  $unitPrice
     * @param int    $quantity
     * @param float  $vat
     *
     * @return Product
     */
    public function createParticipant(
        Event $event,
        string $title = 'Produit Participant',
        float $unitPrice = 100,
        int $quantity = 2,
        float $vat = 20
    ): Product {
        $product = Product::createParticipant($event, $title, $unitPrice, $vat, $quantity);

        foreach ($event->getLocales() as $locale) {
            $product->translate($locale, $title, '', '', '', '');
        }

        $this->productRepository->add($product);

        return $product;
    }

    /**
     * @param Event  $event
     * @param string $title
     * @param float  $unitPrice
     * @param float  $vat
     *
     * @return Product
     */
    public function createPlan(Event $event, string $title = 'Formule', float $unitPrice = 567, float $vat = 20): Product
    {
        $product = Product::createPlan($event, $title, '', $unitPrice, $vat, 15, 30);

        foreach ($event->getLocales() as $locale) {
            $product->translate($locale, $title, '', '', '', '');
        }

        $this->productRepository->add($product);

        return $product;
    }

    /**
     * @param Product $plan
     * @param Product $productParticipant
     * @param int     $quantity
     */
    public function assignProductParticipantToPlan(Product $plan, Product $productParticipant, int $quantity)
    {
        if (!$plan->isPlan() || !$productParticipant->isParticipant()) {
            throw new \InvalidArgumentException('The given products have not the right type');
        }

        $plan->includeProduct($productParticipant, $quantity);

        $this->productRepository->update($plan);
    }

    /**
     * @param Event  $event
     * @param string $title
     * @param float  $unitPrice
     * @param float  $vat
     * @param int    $quantityMax
     *
     * @return Product
     */
    public function createPlanning(
        Event $event,
        string $title,
        float $unitPrice,
        float $vat = 20,
        int $quantityMax = 2
    ): Product {
        $product = Product::createPlanning($event, $title, $unitPrice, $vat, $quantityMax);

        foreach ($event->getLocales() as $locale) {
            $product->translate($locale, $title, '', '', '', '');
        }

        $this->productRepository->add($product);

        return $product;
    }

    public function createOption(
        Event $event,
        string $title,
        float $unitPrice,
        float $vat = 20,
        ?int $quantityMax = null,
        ?int $availabilityCurrent = null,
        ?int $availabilityMax = null,
        \DateTimeInterface $deletableUntil = null,
        bool $subjectedToValidation = false,
        \DateTimeInterface $buyableUntil = null,
        bool $attributable = false
    ): Product {
        $product = Product::createOption(
            $event,
            $title,
            '',
            $unitPrice,
            $vat,
            $quantityMax,
            $availabilityCurrent,
            $availabilityMax,
            true,
            $deletableUntil,
            $subjectedToValidation,
            $buyableUntil,
            $attributable
        );

        foreach ($event->getLocales() as $locale) {
            $product->translate($locale, $title, '', '', '', '');
        }

        $this->productRepository->add($product);

        return $product;
    }
}
