<?php

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\SMSFactory;

class ReminderHandler
{
    const DELAY_BETWEEN_REMIND_NOTIFICATION_IN_MINUTES = 120;

    /** @var EventRepositoryInterface */
    private $eventRepository;

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

    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /**
     * @param EventRepositoryInterface          $eventRepository
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     * @param SheetRepositoryInterface          $sheetRepository
     * @param \DateTimeInterface                $dateTime
     * @param SMSSenderInterface                $SMSSender
     * @param SMSFactory                        $SMSFactory
     * @param Counter                           $counter
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        SheetRepositoryInterface $sheetRepository,
        \DateTimeInterface $dateTime,
        SMSSenderInterface $SMSSender,
        SMSFactory $SMSFactory,
        Counter $counter
    ) {
        $this->eventRepository     = $eventRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->sheetRepository     = $sheetRepository;
        $this->dateTime            = $dateTime;
        $this->counter             = $counter;
        $this->SMSSender           = $SMSSender;

        $tempDatetime = clone $dateTime;

        $this->maximumPastDateToBeNotified = $tempDatetime->modify('-' . self::DELAY_BETWEEN_REMIND_NOTIFICATION_IN_MINUTES . ' minutes');
        $this->SMSFactory                  = $SMSFactory;
        $this->userEventPhoneRepository    = $userEventPhoneRepository;
    }

    /**
     * @param Remind $command
     */
    public function handle(Remind $command)
    {
        $currentEvents = $this->eventRepository->findByDay($this->dateTime);

        foreach ($currentEvents as $currentEvent) {
            $extraDataIndexedByUserId = $this->getExtraDataIndexedByUserId($currentEvent);

            if (empty($extraDataIndexedByUserId)) {
                continue;
            }

            $usersId = array_keys($extraDataIndexedByUserId);

            $usersEventPhone = $this->userEventPhoneRepository->findValidatedByEventAndUsers(
                $currentEvent,
                $usersId
            );

            foreach ($usersEventPhone as $userEventPhone) {
                $user       = $userEventPhone->getUser();
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

                if (null === $sheet || null === $participant) {
                    continue;
                }

                if (!isset($extraDataIndexedByUserId[$user->getId()])) {
                    continue;
                }

                $countAvailablePendingProposition = $this->counter->getCountAvailablePendingMeetingRequests(
                    $sheet,
                    $participant
                );

                if ($countAvailablePendingProposition > 0) {
                    $extraData = $extraDataIndexedByUserId[$user->getId()];

                    $extraData->update(
                        $this->maximumPastDateToBeNotified->format('Y-m-d H:i:s'),
                        $this->dateTime
                    );

                    $this->extraDataRepository->set($extraData);

                    $this->SMSSender->send(
                        $this->SMSFactory->createPendingProposition(
                            $userEventPhone->getPhone(),
                            $sheet,
                            $currentEvent->getAvailableLocale($user->getLocale()),
                            $countAvailablePendingProposition
                        )
                    );
                }
            }
        }
    }

    /**
     * @param Event $event
     *
     * @return ExtraData[]
     */
    private function getExtraDataIndexedByUserId(Event $event): array
    {
        $lastExtraDataForEvent = $this
            ->extraDataRepository
            ->getForEventNameOlderThanDate(
                $event,
                Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
                $this->maximumPastDateToBeNotified
            );

        $extraDataIndexedByUserId = [];

        foreach ($lastExtraDataForEvent as $extraData) {
            $extraDataIndexedByUserId[$extraData->getUser()->getId()] = $extraData;
        }

        return $extraDataIndexedByUserId;
    }
}
