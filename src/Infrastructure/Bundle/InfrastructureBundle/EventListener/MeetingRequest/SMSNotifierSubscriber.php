<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\CreateRequestEvent;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\SMSFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SMSNotifierSubscriber implements EventSubscriberInterface
{
    /**
     * @var DDayGuesser
     */
    private $ddayGuesser;

    /**
     * @var UserEventPhoneChecker
     */
    private $userEventPhoneChecker;

    /**
     * @var SMSSenderInterface
     */
    private $SMSSender;

    /**
     * @var ExtraDataRepositoryInterface
     */
    private $extraDataRepository;

    /**
     * @var SMSFactory
     */
    private $SMSFactory;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * SMSNotifierSubscriber constructor.
     *
     * @param DDayGuesser                  $ddayGuesser
     * @param UserEventPhoneChecker        $userEventPhoneChecker
     * @param SMSSenderInterface           $SMSSender
     * @param ExtraDataRepositoryInterface $extraDataRepository
     * @param SMSFactory                   $SMSFactory
     * @param \DateTimeInterface           $dateTime
     */
    public function __construct(
        DDayGuesser $ddayGuesser,
        UserEventPhoneChecker $userEventPhoneChecker,
        SMSSenderInterface $SMSSender,
        ExtraDataRepositoryInterface $extraDataRepository,
        SMSFactory $SMSFactory,
        \DateTimeInterface $dateTime
    ) {
        $this->ddayGuesser           = $ddayGuesser;
        $this->userEventPhoneChecker = $userEventPhoneChecker;
        $this->SMSSender             = $SMSSender;
        $this->extraDataRepository   = $extraDataRepository;
        $this->SMSFactory            = $SMSFactory;
        $this->dateTime              = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MEETING_REQUEST_CREATED => 'onMeetingRequestCreated',
        ];
    }

    /**
     * @param CreateRequestEvent $event
     */
    public function onMeetingRequestCreated(CreateRequestEvent $event)
    {
        $eventModel = $event->getRequest()->getEvent();

        if (!$this->ddayGuesser->isItDDayAndFeatureEnabled($eventModel)) {
            return;
        }

        /** @var Participant $participant */
        foreach ($event->getRequest()->getToSheet()->getParticipants() as $participant) {
            $extraData = $this->extraDataRepository->getExtraDataForEventNameAndUser(
                $eventModel,
                Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
                $participant->getUser()
            );

            if (null !== $extraData) {
                continue;
            }

            $userEventPhone = $this->userEventPhoneChecker->getValidatedUserEventPhone(
                $participant->getUser(),
                $eventModel
            );

            if (null !== $userEventPhone) {
                $sheet = $event->getRequest()->getToSheet();

                $this->SMSSender->send($this->SMSFactory->createMeetingRequestReceive(
                    $userEventPhone->getPhone(),
                    $sheet,
                    $participant->getUser()->getLocale()
                ));

                $this->extraDataRepository->add(
                    new ExtraData(
                        $participant->getUser(),
                        $eventModel,
                        Type::MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER,
                        $this->dateTime->format('Y-m-d H:i:s'),
                        $this->dateTime
                    )
                );
            }
        }
    }
}
