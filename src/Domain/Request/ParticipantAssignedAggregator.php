<?php

namespace Proximum\Vimeet\Domain\Request;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantAssignedAggregator
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param RequestRepositoryInterface     $requestRepository
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->requestRepository = $requestRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Participant $participant
     */
    public function aggregateAssignation(Participant $participant)
    {
        $isAssignToAcceptedRequest = $this->requestRepository->participantIsAssignedToAccepted($participant);

        $participant->setHasRequestAssigned($isAssignToAcceptedRequest);

        $this->participantRepository->set($participant);
    }
}
