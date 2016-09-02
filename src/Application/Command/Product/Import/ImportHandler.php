<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product\Import;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ImportHandler
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * @var FileStorageInterface
     */
    private $fileStorage;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param PackageRepositoryInterface $packageRepository
     * @param FileStorageInterface       $fileStorage
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        PackageRepositoryInterface $packageRepository,
        FileStorageInterface $fileStorage,
        \DateTimeInterface $dateTime
    ) {

        $this->productRepository = $productRepository;
        $this->packageRepository = $packageRepository;
        $this->fileStorage       = $fileStorage;
        $this->dateTime          = $dateTime;
    }

    /**
     * @param Import $import
     */
    public function handle(Import $import)
    {
        // Retrieve products and package of the target event
        $products = $this->productRepository->findByEvent($import->event);
        $packages = $this->packageRepository->findByEvent($import->event);

        $newProducts = [];
        foreach ($products as $product) {
            if (!$product->isPlan()) {
                $newProducts[$product->getId()] = $this->getNewProduct($product, $import->currentEvent);
            }
        }

        foreach ($products as $product) {
            if ($product->isPlan()) {
                $plan = $this->getNewProduct($product, $import->currentEvent);

                $this->handlePlan($newProducts, $product, $plan, $import->currentEvent->getLocales());
                $newProducts[$product->getId()] = $plan;
            }
        }

        foreach ($newProducts as $product) {
            $this->productRepository->add($product);
        }

        foreach ($packages as $package) {
            $newPackage = $this->getNewPackage($newProducts, $package, $import->currentEvent);
            $this->packageRepository->add($newPackage);
        }
    }

    /**
     * @param Product $oldProduct
     * @param Event   $event
     *
     * @return Product
     */
    private function getNewProduct(Product $oldProduct, Event $event)
    {
        $image = $this->fileStorage->copyAndRename($oldProduct->getImage());

        $product = new Product(
            $event,
            $oldProduct->getType(),
            $oldProduct->getName(),
            $image,
            $oldProduct->getUnitPrice(),
            $oldProduct->getQuantityMax(),
            $oldProduct->getAvailabilityCurrent(),
            $oldProduct->getAvailabilityMax(),
            $oldProduct->isUpdatable(),
            $oldProduct->getDeletableUntil(),
            $oldProduct->isSubjectedToValidation(),
            $oldProduct->getBuyableUntil()
        );

        $locales = $event->getLocales();
        foreach ($locales as $locale) {
            $product->translate(
                $locale,
                $oldProduct->getTitle($locale),
                $oldProduct->getHeading($locale),
                $oldProduct->getDescription($locale),
                $oldProduct->getAddon($locale),
                $oldProduct->getSubjectedToValidationHelp($locale)
            );
        }

        $oldFeatures = $oldProduct->getFeatures();
        foreach ($oldFeatures as $oldFeature) {
            $newFeature = new Product\Feature($product);

            foreach ($locales as $locale) {
                $newFeature->translate($locale, $oldFeature->getTitle($locale), $oldFeature->getDescription($locale));
            }
        }

        return $product;
    }

    /**
     * @param array   $newProducts
     * @param Product $oldPlan
     * @param Product $plan
     * @param array   $locales
     */
    private function handlePlan(array $newProducts, Product $oldPlan, Product $plan, array $locales)
    {
        foreach ($oldPlan->getIncludedProducts() as $includedProduct) {
            $plan->includeProduct(
                $newProducts[$includedProduct->getIncluded()->getId()],
                $includedProduct->getQuantity()
            );
        }

        foreach ($oldPlan->getFeatures() as $feature) {
            $newFeature = new Product\Feature($plan);

            foreach ($locales as $locale) {
                $newFeature->translate($locale, $feature->getTitle($locale), $feature->getDescription($locale));
            }

            $plan->addFeature($newFeature);
        }
    }

    /**
     * @param array   $newProducts
     * @param Package $oldPackage
     * @param Event   $currentEvent
     *
     * @return Package
     */
    private function getNewPackage(array $newProducts, Package $oldPackage, Event $currentEvent)
    {
        $package = new Package($currentEvent, $oldPackage->getTitle(), $this->dateTime);

        $locales = $currentEvent->getLocales();

        foreach ($locales as $locale) {
            $package->translate(
                $locale,
                $oldPackage->getPlansLabel($locale),
                $oldPackage->getParticipantAndPlanningLabel($locale),
                $oldPackage->getOptionsLabel($locale)
            );
        }

        $plans = [];
        foreach ($oldPackage->getPlans() as $plan) {
            $plans[] = $newProducts[$plan->getId()];
        }
        $package->setPlans($plans);

        if (null !== $oldPackage->getParticipant()) {
            $package->setParticipant($newProducts[$oldPackage->getParticipant()->getId()]);
        }

        if (null !== $oldPackage->getPlanning()) {
            $package->setPlanning($newProducts[$oldPackage->getPlanning()->getId()]);
        }

        $groups = [];
        foreach ($oldPackage->getGroups() as $group) {
            $newGroup = new PackageGroup($package, $group->getRank());

            foreach ($locales as $locale) {
                $newGroup->translate($locale, $group->getLabel($locale));
            }

            $options = [];
            foreach ($group->getOptions() as $option) {
                $options[] = $newProducts[$option->getId()];
            }
            $newGroup->setOptions($options);

            $groups[] = $newGroup;
        }

        $package->setGroupsModel($groups);

        $package->enable(
            $oldPackage->isPlansEnabled(),
            $oldPackage->isParticipantAndPlanningEnabled(),
            $oldPackage->isOptionsEnabled()
        );

        return $package;
    }
}
