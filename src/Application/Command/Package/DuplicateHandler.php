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
use Proximum\Vimeet\Domain\Model\PackagePlanRank;
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
     */
    public function handle(Duplicate $duplicate)
    {
        $package = new Package($duplicate->event, $duplicate->title, $this->dateTime);

        $groupOptions = [];
        $groupLabels = [];
        /** @var PackageGroup $group */
        foreach($duplicate->package->getGroups() as $group) {
            $groupOptions[] = $group->getOptions();
            $groupLabels[]  = $group->getLabels();
        }

        $package->setGroups($groupOptions, $groupLabels);
        $package->setPlans($duplicate->package->getPlans());

        $this->packageRepository->add($package);
    }
}