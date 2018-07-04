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
use Proximum\Vimeet\Domain\Repository\UserEvent\UserEventViewRepositoryInterface;

class UserEventViewsFactory
{
    /** @var UserEventViewRepositoryInterface */
    private $userEventViewRepository;

    public function __construct(UserEventViewRepositoryInterface $userEventViewRepository)
    {
        $this->userEventViewRepository = $userEventViewRepository;
    }

    /**
     * @return UserEventView[]
     */
    public function getByEvent(Event $event): array
    {
        $results = $this->userEventViewRepository->getByEvent($event);

        $userEventViews = [];

        foreach ($results as $result) {
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

        return $userEventViews;
    }

    /**
     * @param UserEventView[] $userEventViews
     */
    private function addUserEventViewOrSheetToExistingOne(
        &$userEventViews,
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
        } else {
            $userEventViews[$userId] = new UserEventView(
                $eventId,
                $userId,
                $firstName,
                $lastName,
                $email,
                $locale,
                [
                    ['id' => $sheetId]
                ]
            );
        }
    }
}
