<?php

namespace Proximum\Vimeet\Domain\User\Agenda\Phone;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Tip\ConfirmationPhoneTipChecker;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class ValidationRequiredChecker
{
    /** @var ConfirmationPhoneTipChecker */
    private $confirmationPhoneTipChecker;

    /** @var UserEventPhoneChecker */
    private $userEventPhoneChecker;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param ConfirmationPhoneTipChecker $confirmationPhoneTipChecker
     * @param UserEventPhoneChecker       $userEventPhoneChecker
     * @param MeetingRepositoryInterface  $meetingRepository
     * @param \DateTimeInterface          $dateTime
     */
    public function __construct(
        ConfirmationPhoneTipChecker $confirmationPhoneTipChecker,
        UserEventPhoneChecker $userEventPhoneChecker,
        MeetingRepositoryInterface $meetingRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->confirmationPhoneTipChecker = $confirmationPhoneTipChecker;
        $this->userEventPhoneChecker       = $userEventPhoneChecker;
        $this->meetingRepository           = $meetingRepository;
        $this->dateTime                    = $dateTime;
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return bool
     */
    public function handle(Sheet $sheet, User $user): bool
    {
        $event = $sheet->getEvent();

        if ($this->isTipConfirmationPhoneEnabled($event, $sheet->getType())
            && $this->userHasMeeting($user, $event)
            && $this->agendaOnlineDateHasPassed($event)
        ) {
            return !$this->userEventPhoneChecker->isValidated($user, $event);
        }

        return false;
    }

    /**
     * @param Event $event
     * @param Type  $type
     *
     * @return bool
     */
    private function isTipConfirmationPhoneEnabled(Event $event, Type $type): bool
    {
        return $this->confirmationPhoneTipChecker->isEnabled($event, $type);
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    private function agendaOnlineDateHasPassed(Event $event): bool
    {
        return $this->dateTime > $event->getConfiguration()->getAgendaOnlineDate();
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return bool
     */
    private function userHasMeeting(User $user, Event $event): bool
    {
        return $this->meetingRepository->hasMeetingForUserAndEvent($user, $event);
    }
}
