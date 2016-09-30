<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class DuplicateHandler
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
     * UpdateHandler constructor.
     *
     * @param PackageRepositoryInterface $packageRepository
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(PackageRepositoryInterface $packageRepository, \DateTimeInterface $dateTime)
    {
        $this->packageRepository = $packageRepository;
        $this->dateTime          = $dateTime;
    }

    /**
     * @param Duplicate $duplicate
     *
     * @return Package
     */
    public function handle(Duplicate $duplicate)
    {
        $package = new Package($duplicate->event, $duplicate->title, $this->dateTime);

        // handle translations
        foreach ($duplicate->event->getLocales() as $locale) {
            $package->translate(
                $locale,
                $duplicate->package->getPlansLabel($locale),
                $duplicate->package->getParticipantAndPlanningLabel($locale),
                $duplicate->package->getOptionsLabel($locale)
            );
        }

        // handle package group
        $groupOptions = [];
        $groupLabels = [];
        /** @var PackageGroup $group */
        foreach ($duplicate->package->getGroups() as $group) {
            $groupOptions[] = $group->getOptions();
            $groupLabels[]  = $group->getLabels();
        }

        $package->setGroups($groupOptions, $groupLabels);

        // handle package plans
        $package->setPlans($duplicate->package->getPlans());

        // handle package participant
        if (null !== $duplicate->package->getParticipant()) {
            $package->setParticipant($duplicate->package->getParticipant());
        }

        // handle package planning
        if (null !== $duplicate->package->getPlanning()) {
            $package->setPlanning($duplicate->package->getPlanning());
        }

        $this->packageRepository->add($package);
    }
}
