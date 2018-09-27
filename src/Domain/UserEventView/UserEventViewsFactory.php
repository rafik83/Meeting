<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\UserEventView;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEvent\UserEventViewRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class UserEventViewsFactory
{
    /** @var UserEventViewRepositoryInterface */
    private $userEventViewRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var bool */
    private $isEventDataPreloaded = false;

    /** @var ExtraData[] indexed by User id */
    private $preloadedExtraDataVisioIndexedByUserId = [];

    /** @var ExtraData[] indexed by User id */
    private $preloadedExtraDataVisioTestedIndexedByUserId = [];

    public function __construct(
        UserEventViewRepositoryInterface $userEventViewRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        $this->userEventViewRepository = $userEventViewRepository;
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * @return UserEventView[]
     */
    public function getByEvent(Event $event): array
    {
        $this->preloadEvent($event);

        return $this->getUserEventViews($event, $this->userEventViewRepository->getByEvent($event));
    }

    /**
     * @return UserEventView[]
     */
    public function getByEventAndUser(Event $event, User $user): array
    {
        return $this->getUserEventViews(
            $event,
            $this->userEventViewRepository->getAllSheetsByUserAndEvent($user, $event),
            $user
        );
    }

    /**
     * @return UserEventView[]
     */
    private function getUserEventViews(Event $event, array $results, ?User $filteredUser = null): array
    {
        $userEventViews = [];

        foreach ($results as $result) {
            if (null === $filteredUser || $filteredUser->getId() === $result['ownerId']) {
                $this->addUserEventViewOrSheetToExistingOne(
                    $userEventViews,
                    $event->getId(),
                    $result['ownerId'],
                    $result['ownerFirstName'],
                    $result['ownerLastName'],
                    $result['ownerEmail'],
                    $result['ownerLocale'],
                    $result['sheetId']
                );
            }

            if (null === $filteredUser || $filteredUser->getId() === $result['userId']) {
                $this->addUserEventViewOrSheetToExistingOne(
                    $userEventViews,
                    $event->getId(),
                    $result['userId'],
                    $result['userFirstName'],
                    $result['userLastName'],
                    $result['userEmail'],
                    $result['userLocale'],
                    $result['sheetId']
                );
            }
        }

        return $userEventViews;
    }

    /**
     * @param UserEventView[] $userEventViews
     */
    private function addUserEventViewOrSheetToExistingOne(
        array &$userEventViews,
        int $eventId,
        int $userId,
        ?string $firstName,
        ?string $lastName,
        string $email,
        string $locale,
        int $sheetId
    ): void {
        if (isset($userEventViews[$userId])) {
            if (!$userEventViews[$userId]->hasSheetId($sheetId)) {
                $userEventViews[$userId]->addSheet(['id' => $sheetId]);
            }

            return;
        }

        $userEventViews[$userId] = new UserEventView(
            $eventId,
            $userId,
            $firstName,
            $lastName,
            $email,
            $locale,
            $this->isVisio($eventId, $userId),
            $this->isVisioTested($eventId, $userId),
            [
                ['id' => $sheetId],
            ]
        );
    }

    private function isVisio(int $eventId, int $userId): bool
    {
        if ($this->isEventDataPreloaded) {
            return isset($this->preloadedExtraDataVisioIndexedByUserId[$userId]);
        }

        return $this->hasUserEventExtraData($eventId, $userId, Type::IS_PARTICIPANT_VISIO);
    }

    private function isVisioTested(int $eventId, int $userId): bool
    {
        if ($this->isEventDataPreloaded) {
            return isset($this->preloadedExtraDataVisioTestedIndexedByUserId[$userId]);
        }

        return $this->hasUserEventExtraData($eventId, $userId, Type::VISIO_TESTED);
    }

    private function hasUserEventExtraData(int $eventId, int $userId, string $name): bool
    {
        return null !== $this->extraDataRepository->getExtraDataForEventIdNameAndUserId($eventId, $name, $userId);
    }

    private function preloadEvent(Event $event): void
    {
        $this->preloadEventUsersIsVisio($event);
        $this->preloadEventUsersIsVisioTested($event);
        $this->isEventDataPreloaded = true;
    }

    private function preloadEventUsersIsVisio(Event $event): void
    {
        $this->preloadedExtraDataVisioIndexedByUserId = $this->extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId($event->getId(), Type::IS_PARTICIPANT_VISIO);
    }

    private function preloadEventUsersIsVisioTested(Event $event): void
    {
        $this->preloadedExtraDataVisioTestedIndexedByUserId = $this->extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId($event->getId(), Type::VISIO_TESTED);
    }
}
