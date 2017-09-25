<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Exception\Event\NoEventOnCurrentDayException;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class ReminderHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param EventRepositoryInterface     $eventRepository
     * @param UserRepositoryInterface      $userRepository
     * @param ExtraDataRepositoryInterface $extraDataRepository
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        UserRepositoryInterface $userRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->extraDataRepository = $extraDataRepository;
    }

    public function handle(Reminder $command)
    {
        $currentEvents = $this->eventRepository->findByDay($command->dateTime);

        if (empty($currentEvents)) {
            throw new NoEventOnCurrentDayException('There is no event today');
        }


        foreach ($currentEvents as $currentEvent) {
            $usersWithValidatedPhoneAndPendingRequest = $this
                ->userRepository
                ->getUsersByEventWithValidatedPhoneNumberAndPendingRequest($currentEvent);

            if (null === $usersWithValidatedPhoneAndPendingRequest) {
                continue;
            }

            $lastNotificationReminder = $this->extraDataRepository->getLastNotificationReminderByUserAndDate(
                $currentEvent,
                $usersWithValidatedPhoneAndPendingRequest
            );

            if (empty($lastNotificationReminder)) {
                foreach ($usersWithValidatedPhoneAndPendingRequest as $eventId => $user) {
                    $this->extraDataRepository->add(new ExtraData(
                        $user,
                        $currentEvent,
                        Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
                        $command->dateTime->format('Y-m-d H:i:s'),
                        $command->dateTime
                    ));

                    // envoi sms
                }
            }

            foreach ($lastNotificationReminder as $notificationReminder) {
                if (new \DateTime($notificationReminder->getValue()) >= $command->nextNotificationDatetime) {
                    // envoi sms

                    $this->extraDataRepository->set(
                        $notificationReminder->setValue(
                            $command->nextNotificationDatetime->format('Y-m-d H:i:s')
                        )
                    );
                }
            }

        }
    }
}
