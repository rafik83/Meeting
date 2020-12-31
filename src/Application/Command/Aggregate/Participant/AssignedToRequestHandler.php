<?php

namespace Proximum\Vimeet\Application\Command\Aggregate\Participant;

use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Request\ParticipantAssignedAggregator;

class AssignedToRequestHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantAssignedAggregator */
    private $participantAssignedAggregator;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param ParticipantAssignedAggregator  $participantAssignedAggregator
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantAssignedAggregator $participantAssignedAggregator
    ) {
        $this->participantRepository = $participantRepository;
        $this->participantAssignedAggregator = $participantAssignedAggregator;
    }

    /**
     * @param AssignedToRequest $assignedToRequest
     */
    public function handle(AssignedToRequest $assignedToRequest)
    {
        $participants = $this->participantRepository->findByEventAndInCatalog($assignedToRequest->event);

        foreach ($participants as $participant) {
            $this->participantAssignedAggregator->aggregateAssignation($participant);
        }
    }
}
