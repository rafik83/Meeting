<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Exception\Event\NoEventOnCurrentDayException;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\SMSFactory;

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

    /** @var Counter */
    private $counter;

    /** @var SMSSenderInterface */
    private $SMSSender;

    /** @var SMSFactory */
    private $SMSFactory;

    /**
     * @param EventRepositoryInterface     $eventRepository
     * @param UserRepositoryInterface      $userRepository
     * @param ExtraDataRepositoryInterface $extraDataRepository
     * @param SheetRepositoryInterface     $sheetRepository
     * @param \DateTimeInterface           $dateTime
     * @param SMSSenderInterface           $SMSSender
     * @param SMSFactory                   $SMSFactory
     * @param Counter                      $counter
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        UserRepositoryInterface $userRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        SheetRepositoryInterface $sheetRepository,
        \DateTimeInterface $dateTime,
        SMSSenderInterface $SMSSender,
        SMSFactory $SMSFactory,
        Counter $counter
    ) {
        $this->eventRepository     = $eventRepository;
        $this->userRepository      = $userRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->sheetRepository     = $sheetRepository;
        $this->dateTime            = $dateTime;
        $this->counter             = $counter;
        $this->SMSSender           = $SMSSender;

        $tempDatetime = clone $dateTime;

        $this->maximumPastDateToBeNotified = $tempDatetime->modify('-' . self::DELAY_BETWEEN_REMIND_NOTIFICATION_IN_MINUTES . ' minutes');
        $this->SMSFactory                  = $SMSFactory;
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

            $lastNotificationsByUser = $this
                ->extraDataRepository
                ->getForEventNameAndUsersOlderThanDate(
                    $currentEvent,
                    Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
                    $usersWithValidatedPhoneAndPendingRequest,
                    $this->maximumPastDateToBeNotified
                );

            foreach ($lastNotificationsByUser as $extraData) {
                $user       = $extraData->getUser();
                $userSheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $currentEvent);

                $sheet       = null;
                $participant = null;
                foreach ($userSheets as $userSheet) {
                    $sheet = $userSheet;

                    if ($userSheet->hasUserParticipant($user)) {
                        $participant = $userSheet->getUserParticipant($user);

                        break;
                    }
                }

                if ($sheet === null || $participant === null) {
                    continue;
                }

                $countAvailablePendingProposition = $this->counter->getCountAvailablePendingMeetingRequests(
                    $sheet,
                    $participant
                );

                if ($countAvailablePendingProposition > 0) {
                    $extraData->update(
                        $this->maximumPastDateToBeNotified->format('Y-m-d H:i:s'),
                        $this->dateTime
                    );

                    $this->extraDataRepository->set($extraData);

                    $this->SMSSender->send(
                        $this->SMSFactory->createPendingProposition(
                            '',
                            $sheet,
                            $user->getLocale(),
                            $countAvailablePendingProposition
                        )
                    );
                }
            }
        }
    }
}
