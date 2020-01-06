<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;

class UpdateHandler
{
    /**
     * @var MassRepositoryInterface
     */
    private $massRepository;

    /** @var JobQueueInterface */
    private $jobQueueAdapter;

    /**
     * @param MassRepositoryInterface $massRepository
     * @param JobQueueInterface       $jobQueueAdapter
     */
    public function __construct(MassRepositoryInterface $massRepository, JobQueueInterface $jobQueueAdapter)
    {
        $this->massRepository = $massRepository;
        $this->jobQueueAdapter = $jobQueueAdapter;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $update->mass->update(
            $update->category,
            $update->name,
            $update->begin,
            $update->end,
            $update->blocking,
            $update->dispatch,
            $update->timeSlots,
            $update->types
        );

        foreach ($update->translations as $locale => $translation) {
            $update->mass->updateTranslation($locale, $translation['title'], $translation['description']);
        }

        $this->massRepository->update($update->mass);

        // S'inspirer de $this->userRepository->findByEventWithoutDispatch($mass->getEvent(), $mass);
        // $usersDispatched = $this->userRepository->findByEventWithDispatch($mass);
        // foreach ($usersDispatched as $user) {
        //    $userTypes = $this->typeRepository->getTypesByUserIds($event, [$user->getId()]);
        //    if (!$mass->hasAtLeastOneType($userTypes)) {
        //        // delete User MassAssignment
        //        $massAssignmentRepository->removeByUserAndMass($user, $mass);
        //    }

        $this->jobQueueAdapter->aggregateEventUsersFullUnavailability($update->mass->getEvent());
        $this->jobQueueAdapter->aggregateAvailableSlot($update->mass->getEvent());
    }
}
