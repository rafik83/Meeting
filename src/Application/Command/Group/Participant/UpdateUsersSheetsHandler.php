<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Group\Participant;

use Proximum\Vimeet\Application\Query\Group\Participant\UsersParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\UsersParticipantViewQueryHandler;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\UserToParticipant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class UpdateUsersSheetsHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var UsersParticipantViewQueryHandler */
    private $usersParticipantViewQueryHandler;

    /** @var UserToParticipant */
    private $userToParticipant;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        UserRepositoryInterface $userRepository,
        UsersParticipantViewQueryHandler $usersParticipantViewQueryHandler,
        UserToParticipant $userToParticipant
    ) {
        $this->participantRepository = $participantRepository;
        $this->userRepository = $userRepository;
        $this->usersParticipantViewQueryHandler = $usersParticipantViewQueryHandler;
        $this->userToParticipant = $userToParticipant;
    }

    /**
     * @param UpdateUsersSheets $updateUsersSheets
     *
     * @throws \DomainException
     */
    public function handle(UpdateUsersSheets $updateUsersSheets)
    {
        $userParticipantViews = $this->usersParticipantViewQueryHandler->handle(
            new UsersParticipantViewQuery($updateUsersSheets->group)
        );

        $userIds = array_keys($userParticipantViews);
        $usersIndexedById = $this->userRepository->getByIdsIndexedById($userIds);

        foreach ($updateUsersSheets->sheetsByUser as $userId => $sheets) {
            if (!isset($usersIndexedById[$userId]) || !$usersIndexedById[$userId] instanceof User) {
                throw new \DomainException(sprintf('User with id %s not found', $userId));
            }

            $user = $usersIndexedById[$userId];

            // Create Participant to new assigned sheet
            foreach ($sheets as $sheet) {
                if (false === in_array($sheet, $userParticipantViews[$userId]->sheets)) {
                    $this->userToParticipant->handle($sheet, $user);
                }
            }

            // Remove Participant to unassigned sheet
            foreach ($userParticipantViews[$userId]->sheets as $sheet) {
                if (false === in_array($sheet, $sheets)) {
                    $participantToDelete = $this->participantRepository->getParticipantForUserAndSheet($user, $sheet);

                    if (null !== $participantToDelete) {
                        $this->participantRepository->delete($participantToDelete);
                    }
                }
            }
        }
    }
}
