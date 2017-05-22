<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

interface HappeningParticipationRepositoryInterface
{
    /**
     * @param HappeningParticipation $happeningParticipation
     */
    public function add(HappeningParticipation $happeningParticipation);

    /**
     * @param HappeningParticipation $happeningParticipation
     */
    public function remove(HappeningParticipation $happeningParticipation);

    /**
     * @param User $user
     * @param array       $filters
     *
     * @return HappeningParticipation[]
     */
    public function findByUser(User $user, array $filters = []);

    /**
     * @param User[] $users
     *
     * @return HappeningParticipation[]
     */
    public function findByUsers(array $users);

    /**
     * @param Happening $happening
     *
     * @return int
     */
    public function countParticipationByHappening(Happening $happening);

    /**
     * @param Event $event
     *
     * @return HappeningParticipation[]
     */
    public function getByEvent(Event $event);

    /**
     * @param Sheet $sheet
     *
     * @return HappeningParticipation[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Event $event
     *
     * @return array
     */
    public function countParticipationByEvent(Event $event);

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function countParticipationsBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasParticipationsBySheet(Sheet $sheet);

    /**
     * @param Sheet       $sheet
     * @param Happening[] $happenings
     *
     * @return HappeningParticipation[]
     */
    public function getParticipationsForSheet(Sheet $sheet, $happenings);

    /**
     * @param Participant $participant
     * @param Happening   $happening
     */
    public function removeParticipantForHappening(Participant $participant, Happening $happening);

    /**
     * @param Participant $participant
     *
     * @return null|int
     */
    public function checkAnyParticipation(Participant $participant);

    /**
     * @param HappeningParticipation $happeningParticipation
     */
    public function update(HappeningParticipation $happeningParticipation);
}
