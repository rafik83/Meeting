<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageTranslation;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class CreateHandler
{
    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * @var FileStorageInterface
     */
    private $fileStorage;

    /**
     * @param PackageRepositoryInterface $packageRepository
     * @param FileStorageInterface       $fileStorage
     */
    public function __construct(PackageRepositoryInterface $packageRepository, FileStorageInterface $fileStorage)
    {
        $this->packageRepository = $packageRepository;
        $this->fileStorage       = $fileStorage;
    }

    /**
     * @param Create $create
     */
    public function handle(Create $create)
    {
        // Create the package
        $package = new Package(
            $create->event,
            $create->name,
            $this->fileStorage->upload($create->file),
            $create->unitPrice,
            $create->availabilityCurrent,
            $create->availabilityMax,
            $create->participantIncluded
        );

        // Deal with the translations of the package
        foreach ($create->translations as $locale => $translation) {
            $package->translate(
                $locale,
                $translation['title'],
                $translation['descriptionTitle'],
                $translation['descriptionContent'],
                $translation['optionalPriceText']
            );
        }

        // Deal with the collection of products included in the package
        foreach ($create->productIncluded as $key => $productIncluded) {
            $package->includeProduct($productIncluded['product'], $productIncluded['quantity']);
        }

        // Deal with the collection of features included in the package
        foreach ($create->features as $feature) {
            if (isset($feature['translations'])) {
                $featureObject = new Package\Feature($package);

                foreach ($feature['translations'] as $locale => $translation) {
                    $featureObject->translate($locale, $translation['title'], $translation['description']);
                }

                $package->addFeature($featureObject);
            }
        }

        $this->packageRepository->add($package);
    }
}
