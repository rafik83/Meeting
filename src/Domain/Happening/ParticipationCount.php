<?php

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class ParticipationCount
{
    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @var array
     */
    private $participationCounts = [];

    /**
     * ParticipationCount constructor.
     *
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     */
    public function __construct(HappeningParticipationRepositoryInterface $happeningParticipationRepository)
    {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    /**
     * @param Event $event
     */
    public function loadParticipationCountsFromEvent(Event $event)
    {
        $this->participationCounts = $this
            ->happeningParticipationRepository
            ->countParticipationByEvent($event)
        ;
    }

    /**
     * Return the remaining available participant count of the happening
     *
     * @param Happening $happening
     *
     * @return float|int
     */
    public function getRemaining(Happening $happening)
    {
        if (null === $happening->getLimitParticipant()) {
            return INF;
        }

        if (isset($this->participationCounts[$happening->getId()])) {
            $participationCount = $this->participationCounts[$happening->getId()];
        } else {
            $participationCount = $this
                ->happeningParticipationRepository
                ->countParticipationByHappening($happening)
            ;
        }

        return max(0, $happening->getLimitParticipant() - $participationCount);
    }

    /**
     * @param Happening $happening
     *
     * @return bool
     */
    public function isFull(Happening $happening)
    {
        if (null === $happening->getLimitParticipant()) {
            return false;
        }

        return 0 === $this->getRemaining($happening);
    }
}
