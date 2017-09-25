<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Exception\Event\NoEventOnCurrentDayException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Infrastructure\Adapter\SMSSenderAdapter;

class ReminderHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var SMSSenderAdapter */
    private $SMSSenderAdapter;

    /** @var TranslatorInterface */
    private $translator;

    /** @var RouterInterface */
    private $router;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /**
     * @param EventRepositoryInterface       $eventRepository
     * @param UserRepositoryInterface        $userRepository
     * @param ExtraDataRepositoryInterface   $extraDataRepository
     * @param SheetRepositoryInterface       $sheetRepository
     * @param RequestRepositoryInterface     $requestRepository
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param SMSSenderAdapter               $SMSSenderAdapter
     * @param TranslatorInterface            $translator
     * @param RouterInterface                $router
     * @param SheetInfoGuesser               $sheetInfoGuesser
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        UserRepositoryInterface $userRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        SheetRepositoryInterface $sheetRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SMSSenderAdapter $SMSSenderAdapter,
        TranslatorInterface $translator,
        RouterInterface $router,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->sheetRepository = $sheetRepository;
        $this->requestRepository = $requestRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->SMSSenderAdapter = $SMSSenderAdapter;
        $this->translator = $translator;
        $this->router = $router;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param Reminder $command
     *
     * @throws NoEventOnCurrentDayException
     */
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

            $lastNotificationReminder = $this->getNotificationRemindersIndexedByUserId($currentEvent, $usersWithValidatedPhoneAndPendingRequest);

            foreach ($usersWithValidatedPhoneAndPendingRequest as $user) {
                $userSheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $currentEvent);

                // Get the first sheet for current user
                $sheet = reset($userSheets);

                $countAvailablePendingProposition = $this->getCountAvailablePendingMeetingRequests(
                    $sheet,
                    $command->currentDateTimePlus10Minutes
                );

                if ($countAvailablePendingProposition > 0) {
                    if (!isset($lastNotificationReminder[$user->getId()])) {
                        $this->sendSms($sheet, $currentEvent, $user, $countAvailablePendingProposition);
                        $this->createExtraData($currentEvent, $user, $command->dateTime);
                    } else {
                        $diffLastNotificationInHour = $this->getDiffLastNotificationDateTimeInHour(
                            $lastNotificationReminder[$user->getId()],
                            $command->nextNotificationDatetime)
                        ;

                        if ($diffLastNotificationInHour >= 2) {
                            $this->sendSms($sheet, $currentEvent, $user, $countAvailablePendingProposition);
                            $this->setExtraData(
                                $lastNotificationReminder[$user->getId()],
                                $command->nextNotificationDatetime->format('Y-m-d H:i:s')
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param ExtraData          $lastNotificationReminder
     * @param \DateTimeInterface $nextNotificationDateTime
     *
     * @return int
     */
    private function getDiffLastNotificationDateTimeInHour(
        ExtraData $lastNotificationReminder,
        \DateTimeInterface $nextNotificationDateTime
    ): int {
        $dateTime = new \DateTime($lastNotificationReminder->getValue());

        return $dateTime->diff($nextNotificationDateTime)->h;
    }

    /**
     * Return the count of pending meeting request with available slot
     * in common with the sheet met and available slot begin date superior to current dateTime plus 10 minutes
     *
     * @param Sheet              $sheet
     * @param \DateTimeInterface $currentDatePlus10Minutes
     *
     * @return int
     */
    private function getCountAvailablePendingMeetingRequests(
        Sheet $sheet,
        \DateTimeInterface $currentDatePlus10Minutes
    ): int {
        $availableSlots = $this->meetingSlotRepository->findByIds($sheet->getAvailableSlots());
        $pendingPropositions = $this->requestRepository->getPendingPropositionReceivedBySheet($sheet);
        $countAvailablePendingProposition = 0;

        /** @var Request $pendingProposition */
        foreach ($pendingPropositions as $pendingProposition) {
            $hasOneSlotAvailable = false;
            $sheetMetAvailableSlots = $this->meetingSlotRepository->findByIds($pendingProposition->getFromSheet()->getAvailableSlots());

            /** @var MeetingSlot $sheetMetAvailableSlot */
            foreach ($sheetMetAvailableSlots as $sheetMetAvailableSlot) {
                if (in_array($sheetMetAvailableSlot, $availableSlots, true)
                    && $sheetMetAvailableSlot->getBegin() >= $currentDatePlus10Minutes
                ) {
                    $hasOneSlotAvailable = true;
                }
            }

            if ($hasOneSlotAvailable) {
                $countAvailablePendingProposition++;
            }
        }

        return $countAvailablePendingProposition;
    }

    /**
     * @param Event $event
     * @param array $users
     *
     * @return ExtraData[]
     */
    public function getNotificationRemindersIndexedByUserId(Event $event, array $users): array
    {
        $notificationReminders = $this->extraDataRepository->getLastNotificationReminderByUsersByEvent($event, $users);
        $notificationRemindersIndexedById = [];

        foreach ($notificationReminders as $notificationReminder) {
            $notificationRemindersIndexedById[$notificationReminder->getUser()->getId()] = $notificationReminder;
        }

        return $notificationRemindersIndexedById;
    }

    /**
     * @param ExtraData $extraData
     * @param string    $value
     */
    public function setExtraData(ExtraData $extraData, string $value)
    {
        $this->extraDataRepository->set($extraData->setValue($value));
    }

    /**
     * @param Event              $event
     * @param User               $user
     * @param \DateTimeInterface $dateTime
     */
    private function createExtraData(
        Event $event,
        User $user,
        \DateTimeInterface $dateTime
    ): void {
        $this->extraDataRepository->add(new ExtraData(
            $user,
            $event,
            Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
            $dateTime->format('Y-m-d H:i:s'),
            $dateTime
        ));
    }

    /**
     * @param Sheet $sheet
     * @param Event $event
     * @param User  $user
     * @param int   $countPendingMeetingRequest
     */
    private function sendSms(Sheet $sheet, Event $event, User $user, int $countPendingMeetingRequest): void
    {
        $locale = $event->getAvailableLocale($user->getLocale());
        $message = $this->buildMessage($event, $sheet, $countPendingMeetingRequest, $locale);

        $this->SMSSenderAdapter->send(
            new SMS($user->getMobile(), $message)
        );
    }

    /**
     * Build message like
     *
     * Les Rendez-Vous Carnot: vous avez reçu X propositions de RDVs,
     * voir https://gdr => lien vers GDR de la fiche "Propositions reçues" filtrées sur les disponibles
     *
     * @param Event  $event
     * @param Sheet  $sheet
     * @param int    $countPendingMeetingRequest
     * @param string $locale
     *
     * @return string
     */
    private function buildMessage(
        Event $event,
        Sheet $sheet,
        int $countPendingMeetingRequest,
        string $locale
    ): string {
        $meetingRequestManagementUrl = $this->router->generate(
                'event_meeting_list_request',
                [
                    'sheet' => $sheet->getId(),
                    'state' => 'receive',
                ]
            );

        return $this
            ->translator
            ->trans(
                'sms.reminder.pending_meeting_request',
                [
                    '%eventTitle%'                  => $event->getTitle(),
                    '%sheetTitle%'                  => $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale),
                    '%countPendingMeetingRequest%'  => $countPendingMeetingRequest,
                    '%meetingRequestManagementUrl%' => $meetingRequestManagementUrl,
                ],
                null,
                $locale
            )
        ;
    }
}
