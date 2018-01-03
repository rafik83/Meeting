<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class UpdateHandler
{
    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param PackageRepositoryInterface $packageRepository
     */
    public function __construct(PackageRepositoryInterface $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $update->package
            ->setTitle($update->title)
            ->enable($update->plans->enabled, $update->participantAndPlanning->enabled, $update->options->enabled)
            ->setPlans(array_values($update->plans->plans))
            ->setParticipants($update->participantAndPlanning->participants)
            ->setGroups($update->options->getGroupOptions(), $update->options->getGroupLabels())
        ;

        if (null !== $update->participantAndPlanning->planning) {
           $update->package->setPlanning($update->participantAndPlanning->planning);
        }

        foreach ($update->package->getEvent()->getLocales() as $locale) {
            $update->package->translate(
                $locale,
                $update->plans->getLabel($locale),
                $update->participantAndPlanning->getLabel($locale),
                $update->options->getLabel($locale)
            );
        }

        $this->packageRepository->set($update->package);
    }
}
