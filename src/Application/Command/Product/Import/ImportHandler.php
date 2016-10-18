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
        $fromProducts = $this->productRepository->findByEvent($import->event);
        $fromPackages = $this->packageRepository->findByEvent($import->event);

        $toProducts = [];
        foreach ($fromProducts as $fromProduct) {
            if (!$fromProduct->isPlan()) {
                $toProducts[$fromProduct->getId()] = $this->getToProduct($fromProduct, $import->toEvent);
            }
        }

        foreach ($fromProducts as $fromPlan) {
            if ($fromPlan->isPlan()) {
                $toPlan = $this->getToProduct($fromPlan, $import->toEvent);

                $this->handlePlan($toProducts, $fromPlan, $toPlan);
                $toProducts[$fromPlan->getId()] = $toPlan;
            }
        }

        foreach ($toProducts as $toProduct) {
            $this->productRepository->add($toProduct);
        }

        foreach ($fromPackages as $fromPackage) {
            $toPackage = $this->getNewPackage($toProducts, $fromPackage, $import->toEvent);
            $this->packageRepository->add($toPackage);
        }
    }

    /**
     * @param Product $fromProduct
     * @param Event   $event
     *
     * @return Product
     */
    private function getToProduct(Product $fromProduct, Event $event)
    {
        $image = $this->fileStorage->copyAndRename($fromProduct->getImage());

        $toProduct = new Product(
            $event,
            $fromProduct->getType(),
            $fromProduct->getName(),
            $image,
            $fromProduct->getUnitPrice(),
            $fromProduct->getRowQuantityMax(),
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
    private function handlePlan(array &$toProducts, Product $fromPlan, Product $toPlan)
    {
        foreach ($fromPlan->getIncludedProducts() as $includedProduct) {
            $toPlan->includeProduct(
                $toProducts[$includedProduct->getIncluded()->getId()],
                $includedProduct->getQuantity()
            );
        }
    }

    /**
     * @param array   $toProducts
     * @param Package $fromPackage
     * @param Event   $toEvent
     *
     * @return Package
     */
    private function getNewPackage(array &$toProducts, Package $fromPackage, Event $toEvent)
    {
        $toPackage = new Package($toEvent, $fromPackage->getTitle(), $this->dateTime);

        $locales = $toEvent->getLocales();

        foreach ($locales as $locale) {
            $toPackage->translate(
                $locale,
                $fromPackage->getPlansLabel($locale),
                $fromPackage->getParticipantAndPlanningLabel($locale),
                $fromPackage->getOptionsLabel($locale)
            );
        }

        $plans = [];
        foreach ($fromPackage->getPlans() as $plan) {
            $plans[] = $toProducts[$plan->getId()];
        }
        $toPackage->setPlans($plans);

        if (null !== $fromPackage->getParticipant()) {
            $toPackage->setParticipant($toProducts[$fromPackage->getParticipant()->getId()]);
        }

        if (null !== $fromPackage->getPlanning()) {
            $toPackage->setPlanning($toProducts[$fromPackage->getPlanning()->getId()]);
        }

        $groups = [];
        foreach ($fromPackage->getGroups() as $fromGroup) {
            $toGroup = new PackageGroup($toPackage, $fromGroup->getRank());

            foreach ($locales as $locale) {
                $toGroup->translate($locale, $fromGroup->getLabel($locale));
            }

            $options = [];
            foreach ($fromGroup->getOptions() as $option) {
                $options[] = $toProducts[$option->getId()];
            }
            $toGroup->setOptions($options);

            $groups[] = $toGroup;
        }

        $toPackage->setGroupsModel($groups);

        $toPackage->enable(
            $fromPackage->isPlansEnabled(),
            $fromPackage->isParticipantAndPlanningEnabled(),
            $fromPackage->isOptionsEnabled()
        );

        return $toPackage;
    }
}
