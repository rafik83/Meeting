<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Security;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class CanPasswordBeDefinedWithActivationEmail
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->participantRepository = $participantRepository;
        $this->userRepository = $userRepository;
    }

    public function isSatisfiedBy(Event $event, string $email): bool
    {
        $user = $this->userRepository->findByEmail($email);
        if (null === $user) {
            return false;
        }

        if (false === empty($user->getPassword())) {
            return false;
        }

        $participants = $this->participantRepository->getAllParticipantForUser($event, $user);

        $isImported = false;

        foreach ($participants as $participant) {
            if ($participant->isImported()) {
                $isImported = true;
                break;
            }
        }

        return $isImported;
    }
}
