<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Event\Meeting\CanceledEvent;
use Proximum\Vimeet\Application\Event\Meeting\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Meeting\ParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\Meeting\RequestCanceledEvent;
use Proximum\Vimeet\Application\Event\Meeting\RequestRefusedEvent;
use Proximum\Vimeet\Application\Event\Meeting\RequestSentEvent;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Repository\NotificationRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationEventListener implements EventSubscriberInterface
{
    /**
     * @var NotificationRepositoryInterface
     */
    private $notificationRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * NotificationEventListener constructor.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     * @param SheetInfoGuesser                $sheetInfoGuesser
     * @param TranslatorInterface             $translator
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        TranslatorInterface $translator
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->translator             = $translator;
    }

    /**
     * Notify added participant
     *
     * @param ParticipantAddedEvent $event
     */
    public function onParticipantAddedToMeeting(ParticipantAddedEvent $event)
    {
        $notification = new Notification(
            $event->getParticipant()->getSheet()->getEvent(),
            $event->getEmitter(),
            $event->getParticipant()->getUser(),
            $event->getDate(),
            'meeting.participant.added',
            $event->getMessage()
        );

        $this->notificationRepository->add($notification);
    }

    /**
     * Notify removed participant
     *
     * @param ParticipantRemovedEvent $event
     */
    public function onParticipantRemovedFromMeeting(ParticipantRemovedEvent $event)
    {
        $notification = new Notification(
            $event->getParticipant()->getSheet()->getEvent(),
            $event->getEmitter(),
            $event->getParticipant()->getUser(),
            $event->getDate(),
            'meeting.participant.removed',
            $event->getMessage()
        );

        $this->notificationRepository->add($notification);
    }

    /**
     * Notify from parts (the request creator if there isn't from participants)
     *
     * @param RequestRefusedEvent $event
     */
    public function onRequestRefused(RequestRefusedEvent $event)
    {
        $fromParticipants = $event->getRequest()->hasFromParticipants() ?
            $event->getRequest()->getFromParticipants() :
            [$event->getRequest()->getCreator()];

        foreach ($fromParticipants as $participant) {
            $message = $this->translator->trans(
                'notification.meeting_request.refused.' . ($event->getMessage() ? 'withMessage' : 'withoutMessage'),
                [
                    '%sheetName%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getToSheet()),
                    '%message%'   => $event->getMessage(),
                ],
                null,
                $participant->getUser()->getLocale()
            );

            $this->notificationRepository->add(new Notification(
                $event->getRequest()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'meeting_request.refused',
                $message
            ));
        }
    }

    /**
     * Notify to parts (the sheet owner if there isn't to participants)
     *
     * @param RequestCanceledEvent $event
     */
    public function onRequestCanceled(RequestCanceledEvent $event)
    {
        $toParticipants = $event->getRequest()->hasToParticipants() ?
            $event->getRequest()->getToParticipants()->toArray() :
            [$event->getRequest()->getToSheet()->getOwner()];

        foreach ($toParticipants as $participant) {
            $message = $this->translator->trans(
                'notification.meeting_request.canceled.' . ($event->getMessage() ? 'withMessage' : 'withoutMessage'),
                [
                    '%sheetName%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getFromSheet()),
                    '%message%'   => $event->getMessage(),
                ],
                null,
                $participant->getUser()->getLocale()
            );

            $this->notificationRepository->add(new Notification(
                $event->getRequest()->getToSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'meeting_request.canceled',
                $message
            ));
        }
    }

    /**
     * Notify each from and to participants
     *
     * @param CanceledEvent $event
     */
    public function onMeetingCanceled(CanceledEvent $event)
    {
        $participants = array_merge(
            $event->getMeeting()->getFromParticipants()->toArray(),
            $event->getMeeting()->getToParticipants()->toArray()
        );

        foreach ($participants as $participant) {
            $this->notificationRepository->add(new Notification(
                $event->getMeeting()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'metting.canceled',
                $event->getMessage()
            ));
        }
    }

    /**
     * Notify the recipient sheet owner when new request is send
     *
     * @param RequestSentEvent $event
     */
    public function onRequestSent(RequestSentEvent $event)
    {
        $recipient = $event->getRequest()->getToSheet()->getOwner()->getUser();
        $message   = $this->translator->trans(
            'notification.meeting_request.receive.message',
            [
                '%from_sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getFromSheet()),
            ],
            'notification',
            $recipient->getLocale()
        );

        $this->notificationRepository->add(new Notification(
            $event->getRequest()->getFromSheet()->getEvent(),
            $event->getEmitter(),
            $recipient,
            $event->getDate(),
            'metting.canceled',
            $message
        ));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'meeting.participant.added'   => 'onParticipantAddedToMeeting',
            'meeting.participant.removed' => 'onParticipantRemovedFromMeeting',
            'meeting_request.sent'        => 'onRequestSent',
            'meeting_request.refused'     => 'onRequestRefused',
            'meeting_request.canceled'    => 'onRequestCanceled',
            'meeting.canceled'            => 'onMeetingCanceled',
        ];
    }
}
