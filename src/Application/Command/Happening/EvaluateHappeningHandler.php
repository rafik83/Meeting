<?php


namespace Proximum\Vimeet\Application\Command\Happening;


use Proximum\Vimeet\Application\Exception\Happening\HappeningParticipationNotFoundException;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class EvaluateHappeningHandler
{
    private HappeningParticipationRepositoryInterface $happeningParticipationRepository;

    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    public function handle(EvaluateHappening $evaluateHappening) {

        $happeningParticipation = $this->happeningParticipationRepository->findByHappeningAndUser($evaluateHappening->happening, $evaluateHappening->user);
        if (null === $happeningParticipation) {
            throw new HappeningParticipationNotFoundException();
        }
        $happeningParticipation->setEvaluation($evaluateHappening->evaluation);
        $this->happeningParticipationRepository->set($happeningParticipation);
    }
}
