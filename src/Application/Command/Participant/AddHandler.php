<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\BaseHandler;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Application\Exception\Participant\EmailCanNotBeNullException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class AddHandler extends BaseHandler
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
     * @throws EmailCanNotBeNullException
     * @throws ParticipantAlreadyExistException
     * @throws RequiredDataEmptyException
     */
    public function handle(Add $add)
    {
        // Check the constraint on the data (required) before
        $this->checkDataConstraint($add->data, $add->sheet->getType()->getParticipantTemplate());

        if ($add->email === null) {
            throw new EmailCanNotBeNullException();
        }

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

        // Add the new participant
        $participant = new Participant($add->sheet, $user, $add->data, $add->owner, true);
        $this->participantRepository->add($participant);

        $add->participant = $participant;
    }
}
