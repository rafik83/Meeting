<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\UserEvent;

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

    public function getByEvent(Event $event): array
    {
        $results = $this->userEventViewRepository->getByEvent($event);

        $userEventViews = [];

        foreach ($results as $result) {
            $userEventViews[$result['ownerId']] = new UserEventView(
                $event->getId(),
                $result['ownerId'],
                $result['ownerFirstName'],
                $result['ownerLastName'],
                $result['ownerEmail']
            );

            $userEventViews[$result['userId']] = new UserEventView(
                $event->getId(),
                $result['userId'],
                $result['userFirstName'],
                $result['userLastName'],
                $result['userEmail']
            );
        }

        return $userEventViews;
    }
}
