<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\HappeningView;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class HappeningParticipationQueryHandler
{
    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    public function __construct(HappeningParticipationRepositoryInterface $happeningParticipationRepository)
    {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    public function handle(HappeningParticipationQuery $happeningParticipationQuery): void
    {
        $sheet = $happeningParticipationQuery->sheet;
        $user = $happeningParticipationQuery->currentUser;
        $participant = $sheet->getUserParticipant($user);

        $happeningList = [];
        /** @var HappeningView[] $happenings */
        $happenings = [];

        foreach ($happeningParticipationQuery->programView->days as $day) {
            foreach ($day->happenings as $happening) {
                $happeningList[] = $happening->getId();
                $happenings[$happening->getId()] = $happening;
            }
        }

        $happeningParticipations = $this
            ->happeningParticipationRepository
            ->getParticipationsForSheet($sheet, $happeningList);

        /** @var HappeningParticipation $participation */
        foreach ($happeningParticipations as $participation) {
            if (isset($happenings[$participation->getHappening()->getId()])) {
                $happenings[$participation->getHappening()->getId()]->setHasParticipation(true);

                if (null !== $participant && $participation->getUser() === $participant->getUser()) {
                    $happenings[$participation->getHappening()->getId()]->setCurrentUserParticipate(true);
                }
            }
        }
    }
}
