<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Meeting;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\SMSFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SMSNotifierEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var SMSSenderInterface
     */
    private $SMSSender;

    /**
     * @var SMSFactory
     */
    private $SMSFactory;

    /**
     * @var UserEventPhoneChecker
     */
    private $userEventPhoneChecker;

    /**
     * @param SMSSenderInterface    $SMSSender
     * @param SMSFactory            $SMSFactory
     * @param UserEventPhoneChecker $userEventPhoneChecker
     */
    public function __construct(
        SMSSenderInterface $SMSSender,
        SMSFactory $SMSFactory,
        UserEventPhoneChecker $userEventPhoneChecker
    ) {
        $this->SMSSender             = $SMSSender;
        $this->SMSFactory            = $SMSFactory;
        $this->userEventPhoneChecker = $userEventPhoneChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MEETING_CREATED => 'onMeetingCreated',
        ];
    }

    /**
     * @param MeetingCreatedEvent $event
     */
    public function onMeetingCreated(MeetingCreatedEvent $event)
    {
        $meeting = $event->getMeeting();

        if (!$meeting->isCreatedByParticipants()) {
            return;
        }

        /** @var Participant $fromParticipant */
        foreach ($meeting->getFromParticipants() as $fromParticipant) {
            $userEventPhone = $this->userEventPhoneChecker->getValidatedUserEventPhone(
                $fromParticipant->getUser(),
                $meeting->getEvent()
            );

            if (null === $userEventPhone) {
                continue;
            }

            $sms = $this->SMSFactory->createSentMeetingRequestApproved(
                $userEventPhone->getPhone(),
                $meeting,
                $meeting->getFromSheet(),
                $meeting->getToSheet(),
                $fromParticipant,
                $fromParticipant->getLocale()
            );

            $this->SMSSender->send($sms);
        }

        foreach ($meeting->getToParticipants() as $toParticipant) {
            $userEventPhone = $this->userEventPhoneChecker->getValidatedUserEventPhone(
                $toParticipant->getUser(),
                $meeting->getEvent()
            );

            if (null === $userEventPhone) {
                continue;
            }

            $sms = $this->SMSFactory->createReceiveMeetingRequestApproved(
                $userEventPhone->getPhone(),
                $meeting,
                $meeting->getToSheet(),
                $meeting->getFromSheet(),
                $toParticipant,
                $toParticipant->getLocale()
            );

            $this->SMSSender->send($sms);
        }
    }
}
