<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class AddHandler
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @param UserRepositoryInterface        $userRepository
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->userRepository        = $userRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Add $add
     *
     * @throws ParticipantAlreadyExistException
     */
    public function handle(Add $add)
    {
        // Try to find user
        $user = $this->userRepository->findByEmail($add->email);

        // Create user if not exists
        if (null === $user) {
            $user = new User($add->email, '', '', $add->locale);
            $this->userRepository->add($user);
        }

        foreach ($add->sheet->getParticipants() as $participant) {
            if ($participant->getUser() == $user) {
                throw new ParticipantAlreadyExistException('User already linked to this sheet');
            }
        }
        // Add the new partipant
        $participant = new Participant($add->sheet, $user, $add->data, $add->owner);
        $this->participantRepository->add($participant);

        $add->participant = $participant;
    }
}
