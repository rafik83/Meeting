<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Session;

use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class UpdateParticipantSessionManager
{
    const UPDATE_PARTICIPANT_SESSION_DATA = 'update_participant_data';
    const UPDATE_PARTICIPANT__MOBILE      = 'update_participant__mobile';
    const UPDATE_PARTICIPANT__PARTICIPANT = 'update_participant__participant';
    const UPDATE_PARTICIPANT__SHEET       = 'update_participant__sheet';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * UpdateParticipantSessionManager constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param string      $mobile
     */
    public function set(Sheet $sheet, Participant $participant, string $mobile)
    {
        $this->session->set(self::UPDATE_PARTICIPANT_SESSION_DATA, [
            self::UPDATE_PARTICIPANT__MOBILE      => $mobile,
            self::UPDATE_PARTICIPANT__SHEET       => $sheet->getId(),
            self::UPDATE_PARTICIPANT__PARTICIPANT => $participant->getId(),
        ]);
    }

    /**
     * @return null|string
     */
    public function getMobile(): ?string
    {
        $participantData = $this->session->get(self::UPDATE_PARTICIPANT_SESSION_DATA);

        return $participantData[self::UPDATE_PARTICIPANT__MOBILE] ?? null;
    }

    public function remove()
    {
        $this->session->remove(self::UPDATE_PARTICIPANT_SESSION_DATA);
    }
}
