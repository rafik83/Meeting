<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Group\Participant;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Group\Participant\UsersParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\UsersParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Group\Participant\UpdateUsersSheetsResultView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\UserToParticipant;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class UpdateUsersSheetsHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var UsersParticipantViewQueryHandler */
    private $usersParticipantViewQueryHandler;

    /** @var UserToParticipant */
    private $userToParticipant;

    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesserCache;

    /**
     * @param ParticipantRepositoryInterface   $participantRepository
     * @param UserRepositoryInterface          $userRepository
     * @param MeetingRepositoryInterface       $meetingRepository
     * @param RequestRepositoryInterface       $requestRepository
     * @param UsersParticipantViewQueryHandler $usersParticipantViewQueryHandler
     * @param UserToParticipant                $userToParticipant
     * @param SheetInfoGuesserCache            $sheetInfoGuesserCache
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        UserRepositoryInterface $userRepository,
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        UsersParticipantViewQueryHandler $usersParticipantViewQueryHandler,
        UserToParticipant $userToParticipant,
        SheetInfoGuesserCache $sheetInfoGuesserCache
    ) {
        $this->participantRepository = $participantRepository;
        $this->userRepository = $userRepository;
        $this->meetingRepository = $meetingRepository;
        $this->requestRepository = $requestRepository;
        $this->usersParticipantViewQueryHandler = $usersParticipantViewQueryHandler;
        $this->userToParticipant = $userToParticipant;
        $this->sheetInfoGuesserCache = $sheetInfoGuesserCache;
    }

    /**
     * @param UpdateUsersSheets $updateUsersSheets
     *
     * @throws \DomainException
     *
     * @return UpdateUsersSheetsResultView[]
     */
    public function handle(UpdateUsersSheets $updateUsersSheets)
    {
        $updateUsersSheetsResultViews = [];

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
                        if (1 === $sheet->countParticipants()) {
                            $updateUsersSheetsResultViews[] = UpdateUsersSheetsResultView::createSheetMustHaveAtLeastOneParticipant(
                                $userParticipantViews[$userId]->fullname,
                                $this->sheetInfoGuesserCache->guessSheetTitle($sheet, null)
                            );

                            continue;
                        }

                        if (true === $this->requestRepository->hasAssignedRequestByParticipant($participantToDelete)) {
                            $updateUsersSheetsResultViews[] = UpdateUsersSheetsResultView::createHasMeetingRequestOnSheet(
                                $userParticipantViews[$userId]->fullname,
                                $this->sheetInfoGuesserCache->guessSheetTitle($sheet, null)
                            );

                            continue;
                        }

                        if (true === $this->meetingRepository->hasScheduledMeetingByParticipant($participantToDelete)) {
                            $updateUsersSheetsResultViews[] = UpdateUsersSheetsResultView::createHasMeetingOnSheet(
                                $userParticipantViews[$userId]->fullname,
                                $this->sheetInfoGuesserCache->guessSheetTitle($sheet, null)
                            );

                            continue;
                        }

                        $this->participantRepository->delete($participantToDelete);
                        $sheet->removeParticipant($participantToDelete);
                    }
                }
            }
        }

        return $updateUsersSheetsResultViews;
    }
}
