<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Participant;

interface ParticipantRepositoryInterface
{
    /**
     * @param Participant $participant
     */
    public function add(Participant $participant);

    /**
     * @param integer $id
     *
     * @return Participant
     */
    public function findById($id);

    /**
     * @param Participant $participant
     */
    public function set(Participant $participant);

    /**
     * @param $userEmail
     * @param $eventId
     *
     * @return integer
     */
    public function getLastParticipantIdForEventAndUser($userEmail, $eventId);

    /**
     * @param integer $userId
     *
     * @return array
     */
    public function getAllParticipantForUser($userId);

    /**
     * @param integer $sheetId
     *
     * @return array
     */
    public function getParticipantViewsBySheet($sheetId);
}
