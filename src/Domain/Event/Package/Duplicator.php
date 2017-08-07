<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\Package;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class Duplicator
{
    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * Duplicator constructor.
     *
     * @param PackageRepositoryInterface $packageRepository
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        PackageRepositoryInterface $packageRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->packageRepository = $packageRepository;
        $this->dateTime          = $dateTime;
    }

    /**
     * @param Event $event
     * @param array $duplicationHelper
     *
     * @return array
     */
    public function duplicate(Event $event, array $duplicationHelper): array
    {
        $packages = $this->packageRepository->findByEvent($event->getDuplicatedFrom());
        $duplicationHelper['packageTemplate'] = [];

        foreach ($packages as $package) {
            $newPackage = new Package($event, $package->getTitle(), $this->dateTime);
            foreach ($event->getLocales() as $locale) {
                $newPackage->translate(
                    $locale,
                    $package->getPlansLabel($locale),
                    $package->getParticipantAndPlanningLabel($locale),
                    $package->getOptionsLabel($locale)
                );
            }

            $groupOptions = [];
            $groupLabels  = [];

            /** @var PackageGroup $group */
            foreach ($package->getGroups() as $group) {
                $options = [];
                foreach ($group->getOptions() as $product) {
                    $options[] = $duplicationHelper['product'][$product->getId()];
                }

                $groupOptions[] = $options;
                $groupLabels[]  = $group->getLabels();
            }

            $newPackage->setGroups($groupOptions, $groupLabels);
            $plans = [];

            foreach ($package->getPlans() as $plan) {
                $plans[] = $duplicationHelper['product'][$plan->getId()];
            }
            $newPackage->setPlans($plans);

            if (null !== $package->getParticipant()) {
                $newPackage->setParticipant(
                    $duplicationHelper['product'][$package->getParticipant()->getId()]
                );
            }

            if (null !== $package->getPlanning()) {
                $newPackage->setPlanning(
                    $duplicationHelper['product'][$package->getPlanning()->getId()]
                );
            }

            $duplicationHelper['packageTemplate'][$package->getId()] = $newPackage;
            $this->packageRepository->add($newPackage);
        }

        return $duplicationHelper;
    }
}
