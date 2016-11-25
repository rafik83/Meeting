<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Mail;

use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantMailViewQueryHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * ParticipantMailViewQueryHandler constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     * @param ParticipantInfoGuesser         $participantInfoGuesser
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {

        $this->participantRepository  = $participantRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param ParticipantMailViewQuery $query
     *
     * @return ParticipantInfoView
     */
    public function handle(ParticipantMailViewQuery $query)
    {
        if ($query->sheet === null) {
            return $this->userInfoView($query->user);
        }

        $participant = $this->participantRepository->getParticipantForUserAndSheet($query->user, $query->sheet);

        if ($participant === null) {
            return $this->userInfoView($query->user);
        }

        $firstname = $this->participantInfoGuesser->guessParticipantFirstName(
            $participant,
            $query->user->getLocale()
        );

        $lastname = $this->participantInfoGuesser->guessParticipantFirstName(
            $participant,
            $query->user->getLocale()
        );

        return new ParticipantInfoView(
            $firstname !== null ? $firstname : $query->user->getAccount()->getFirstName(),
            $lastname !== null ? $lastname : $query->user->getAccount()->getLastName(),
            $participant->getSheet()->getType()->getTitle($query->user->getLocale())
        );
    }

    /**
     * @param User $user
     *
     * @return ParticipantInfoView
     */
    private function userInfoView(User $user)
    {
        return new ParticipantInfoView(
            $user->getAccount()->getFirstName(),
            $user->getAccount()->getLastName()
        );
    }
}
