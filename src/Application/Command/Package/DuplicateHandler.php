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
     * @var array
     */
    private $defaultLabels;

    /**
     * UpdateHandler constructor.
     *
     * @param PackageRepositoryInterface $packageRepository
     * @param \DateTimeInterface         $dateTime
     * @param array                      $defaultLabels
     */
    public function __construct(PackageRepositoryInterface $packageRepository, \DateTimeInterface $dateTime, array $defaultLabels)
    {
        $this->packageRepository = $packageRepository;
        $this->dateTime          = $dateTime;
        $this->defaultLabels = $defaultLabels;
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
                isset($this->defaultLabels['plans'][$locale]) ? $this->defaultLabels['plans'][$locale] : '',
                isset($this->defaultLabels['participant_and_planning'][$locale]) ? $this->defaultLabels['participant_and_planning'][$locale] : '',
                isset($this->defaultLabels['options'][$locale]) ? $this->defaultLabels['options'][$locale] : ''
            );
        }

        // handle package group
        $groupOptions = [];
        $groupLabels = [];
        /** @var PackageGroup $group */
        foreach($duplicate->package->getGroups() as $group) {
            $groupOptions[] = $group->getOptions();
            $groupLabels[]  = $group->getLabels();
        }

        $package->setGroups($groupOptions, $groupLabels);

        // handle package plans
        $package->setPlans($duplicate->package->getPlans());

        $this->packageRepository->add($package);

        return $package;
    }
}
