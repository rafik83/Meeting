<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class SortParticipantsHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    public function handle(SortParticipants $sortParticipants): void
    {
        $sheet = $sortParticipants->getSheet();
        $participants = $sheet->getParticipantsArray();
        $countParticipants = $sheet->countParticipants();

        foreach ($participants as $participant) {
            $rank = $sortParticipants->getParticipantRank($participant->getId());

            // Inverse rank in order to manage new participant with rank 0
            $participant->setRank(1 + $countParticipants - $rank);

            $this->participantRepository->set($participant);
        }
    }
}
