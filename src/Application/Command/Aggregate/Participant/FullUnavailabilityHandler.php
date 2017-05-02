<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Aggregate\Participant;

use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;

class FullUnavailabilityHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var ParticipantUnavailableAggregator
     */
    private $participantUnavailableAggregator;

    /**
     * @param ParticipantRepositoryInterface   $participantRepository
     * @param ParticipantUnavailableAggregator $participantUnavailableAggregator
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantUnavailableAggregator $participantUnavailableAggregator
    ) {
        $this->participantRepository            = $participantRepository;
        $this->participantUnavailableAggregator = $participantUnavailableAggregator;
    }

    /**
     * @param FullUnavailability $fullUnavailability
     */
    public function handle(FullUnavailability $fullUnavailability)
    {
        if ($fullUnavailability->onlyCatalog) {
            $participants = $this->participantRepository->findByEventAndInCatalog($fullUnavailability->event);
        } else {
            $participants = $this->participantRepository->findByEvent($fullUnavailability->event);
        }

         foreach ($participants as $participant) {
             $this->participantUnavailableAggregator->aggregateUnavailability($participant);
         }
    }
}
