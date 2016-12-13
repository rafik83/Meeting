<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class ParticipationCount
{
    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var HappeningParticipation[]
     */
    private $counts;

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
     * @param Event     $event
     * @param Happening $happening
     *
     * @return double|int
     */
    public function getRemaining(Event $event, Happening $happening)
    {
        if (null === $happening->getLimitParticipant()) {
            return INF;
        }

        if (!$this->event !== $event) {
            $this->event  = $event;
            $this->counts = $this->happeningParticipationRepository->countParticipationByEvent($event);
        }

        $count = isset($this->counts[$happening->getId()]) ? $this->counts[$happening->getId()] : 0;

        return max(0, $happening->getLimitParticipant() - $count);
    }

    /**
     * @param Event     $event
     * @param Happening $happening
     *
     * @return bool
     */
    public function isFull(Event $event, Happening $happening)
    {
        if (null === $happening->getLimitParticipant()) {
            return false;
        }

        return $this->getRemaining($event, $happening) === 0;
    }
}
