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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class ReminderHandler
{
    const DELAY_BETWEEN_REMIND_NOTIFICATION_IN_MINUTES = 120;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var \DateTimeInterface */
    private $maximumPastDateToBeNotified;

    /** @var \DateTimeInterface */
    private $currentDateTimePlus10Minutes;

    /** @var SmsNotification */
    private $smsNotification;

    /** @var Counter */
    private $counter;

    /**
     * @param EventRepositoryInterface       $eventRepository
     * @param UserRepositoryInterface        $userRepository
     * @param ExtraDataRepositoryInterface   $extraDataRepository
     * @param SheetRepositoryInterface       $sheetRepository
     * @param \DateTimeInterface             $dateTime
     * @param SmsNotification                $smsNotification
     * @param Counter                        $counter
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        UserRepositoryInterface $userRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        SheetRepositoryInterface $sheetRepository,
        \DateTimeInterface $dateTime,
        SmsNotification $smsNotification,
        Counter $counter
    ) {
        $this->eventRepository     = $eventRepository;
        $this->userRepository      = $userRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->sheetRepository     = $sheetRepository;
        $this->dateTime            = $dateTime;
        $this->smsNotification     = $smsNotification;
        $this->counter             = $counter;

        $tempDatetime = clone $dateTime;
        $this->maximumPastDateToBeNotified = $tempDatetime->modify('-' . self::DELAY_BETWEEN_REMIND_NOTIFICATION_IN_MINUTES . ' minutes');

        $tempDatetime = clone $dateTime;
        $this->currentDateTimePlus10Minutes = $tempDatetime->modify('+10 minutes');
    }

    /**
     * @param Remind $command
     *
     * @throws NoEventOnCurrentDayException
     */
    public function handle(Remind $command)
    {
        $currentEvents = $this->eventRepository->findByDay($this->dateTime);

        if (empty($currentEvents)) {
            throw new NoEventOnCurrentDayException('There is no event today');
        }

        foreach ($currentEvents as $currentEvent) {
            $usersWithValidatedPhoneAndPendingRequest = $this
                ->userRepository
                ->getUsersByEventWithValidatedPhoneNumberAndPendingRequest($currentEvent);

            if (empty($usersWithValidatedPhoneAndPendingRequest)) {
                continue;
            }

            $lastNotificationByUser = $this->getNotificationRemindersIndexedByUserId(
                $currentEvent,
                $usersWithValidatedPhoneAndPendingRequest
            );

            foreach ($usersWithValidatedPhoneAndPendingRequest as $user) {
                $userSheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $currentEvent);

                // Get the first sheet for current user
                $sheet = reset($userSheets);

                if ($sheet === false) {
                    continue;
                }

                $countAvailablePendingProposition = $this->counter->getCountAvailablePendingMeetingRequests(
                    $sheet,
                    $this->currentDateTimePlus10Minutes
                );

                if ($countAvailablePendingProposition > 0) {
                    $extraData = $lastNotificationByUser[$user->getId()];
                    $extraData->update(
                        $this->maximumPastDateToBeNotified->format('Y-m-d H:i:s'),
                        $this->dateTime
                    );

                    $this->extraDataRepository->set($extraData);
                    $this->smsNotification->sendSms($sheet, $currentEvent, $user, $countAvailablePendingProposition);
                }
            }
        }
    }

    /**
     * @param Event $event
     * @param array $users
     *
     * @return ExtraData[]
     */
    private function getNotificationRemindersIndexedByUserId(Event $event, array $users): array
    {
        $notificationReminders = $this
            ->extraDataRepository
            ->getForEventNameAndUsersOlderThanDate(
                $event,
                Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
                $users,
                $this->maximumPastDateToBeNotified
            )
        ;

        $notificationRemindersIndexedById = [];

        foreach ($notificationReminders as $notificationReminder) {
            $notificationRemindersIndexedById[$notificationReminder->getUser()->getId()] = $notificationReminder;
        }

        return $notificationRemindersIndexedById;
    }

    /**
     * @param Event              $event
     * @param User               $user
     */
    private function createExtraData(
        Event $event,
        User $user
    ): void {
        $this->extraDataRepository->add(new ExtraData(
            $user,
            $event,
            Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
            $this->dateTime->format('Y-m-d H:i:s'),
            $this->dateTime
        ));
    }
}
