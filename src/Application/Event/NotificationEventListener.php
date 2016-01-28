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
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantRemovedEvent as MeetingRequestParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantAddedEvent as MeetingRequestParticipantAddedEvent;
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
     * Notify added participant
     *
     * @param MeetingRequestParticipantAddedEvent $event
     */
    public function onParticipantAddedToMeetingRequest(MeetingRequestParticipantAddedEvent $event)
    {
        $message = $this->translator->trans(
            'notification.meeting_request.participant.added.message',
            [
                '%to_sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getMeetingRequest()->getToSheet()),
                '%message%'  => $event->getMessage(),
            ],
            'notifications',
            $event->getParticipant()->getUser()->getLocale()
        );

        $notification = new Notification(
            $event->getParticipant()->getSheet()->getEvent(),
            $event->getEmitter(),
            $event->getParticipant()->getUser(),
            $event->getDate(),
            'meeting_request.participant.added',
            $message
        );

        $this->notificationRepository->add($notification);
    }

    /**
     * Notify removed participant
     *
     * @param MeetingRequestParticipantRemovedEvent $event
     */
    public function onParticipantRemovedToMeetingRequest(MeetingRequestParticipantRemovedEvent $event)
    {
        $message = $this->translator->trans(
            'notification.meeting_request.participant.removed.message',
            [
                '%sheet_to%' => $this->sheetInfoGuesser->guessSheetInfo($event->getMeetingRequest()->getToSheet()),
                '%message%'  => $event->getMessage(),
            ],
            'notifications',
            $event->getParticipant()->getUser()->getLocale()
        );

        $notification = new Notification(
            $event->getParticipant()->getSheet()->getEvent(),
            $event->getEmitter(),
            $event->getParticipant()->getUser(),
            $event->getDate(),
            'meeting_request.participant.removed',
            $message
        );

        $this->notificationRepository->add($notification);
    }

    /**
     * Notify from participant and the from sheet owner
     *
     * @param RequestRefusedEvent $event
     */
    public function onRequestRefused(RequestRefusedEvent $event)
    {
        $fromSheetOwner   = $event->getRequest()->getFromSheet()->getOwner();
        $fromParticipants = $event->getRequest()->getFromParticipants()->toArray();

        if (!in_array($fromSheetOwner, $fromParticipants)) {
            array_push($fromParticipants, $fromSheetOwner);
        }

        foreach ($fromParticipants as $participant) {
            $message = $this->translator->trans(
                'notification.meeting_request.refused.message',
                [
                    '%to_sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getToSheet()),
                ],
                'notifications',
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
     * Notify from participant and the from sheet owner.
     * Notify to participant and the to sheet owner.
     *
     * @param RequestCanceledEvent $event
     */
    public function onRequestCanceled(RequestCanceledEvent $event)
    {
        $fromSheetOwner   = $event->getRequest()->getFromSheet()->getOwner();
        $fromParticipants = $event->getRequest()->getFromParticipants()->toArray();

        if (!in_array($fromSheetOwner, $fromParticipants)) {
            array_push($fromParticipants, $fromSheetOwner);
        }

        foreach ($fromParticipants as $participant) {
            $message = $this->translator->trans(
                'notification.meeting_request.canceled.to_message',
                [
                    '%sheet_from%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getFromSheet()),
                ],
                'notifications',
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

        $toSheetOwner   = $event->getRequest()->getFromSheet()->getOwner();
        $toParticipants = $event->getRequest()->getFromParticipants()->toArray();

        if (!in_array($toSheetOwner, $toParticipants)) {
            array_push($toParticipants, $toSheetOwner);
        }

        foreach ($toParticipants as $participant) {
            $message = $this->translator->trans(
                'notification.meeting_request.canceled.to_message',
                [
                    '%sheet_from%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getFromSheet()),
                ],
                'notifications',
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
        // Notify each from participants

        $fromSheetOwner   = $event->getRequest()->getFromSheet()->getOwner();
        $fromParticipants = $event->getRequest()->getFromParticipants()->toArray();

        if (!in_array($fromSheetOwner, $fromParticipants)) {
            array_push($fromParticipants, $fromSheetOwner);
        }

        foreach ($fromParticipants as $participant) {
            $message = $this->translator->trans(
                'notification.meeting.canceled',
                [
                    '%sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getToSheet()),
                ],
                'notifications',
                $participant->getUser()->getLocale()
            );

            $this->notificationRepository->add(new Notification(
                $event->getMeeting()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'metting.canceled',
                $message
            ));
        }

        // Notify each to participants

        $toSheetOwner   = $event->getRequest()->getFromSheet()->getOwner();
        $toParticipants = $event->getRequest()->getFromParticipants()->toArray();

        if (!in_array($toSheetOwner, $toParticipants)) {
            array_push($toParticipants, $toSheetOwner);
        }

        foreach ($fromParticipants as $participant) {
            $message = $this->translator->trans(
                'notification.meeting.canceled',
                [
                    '%sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getFromSheet()),
                ],
                'notifications',
                $participant->getUser()->getLocale()
            );

            $this->notificationRepository->add(new Notification(
                $event->getMeeting()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'metting.canceled',
                $message
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
            'meeting.participant.added'           => 'onParticipantAddedToMeeting',
            'meeting.participant.removed'         => 'onParticipantRemovedFromMeeting',
            'meeting_request.sent'                => 'onRequestSent',
            'meeting_request.refused'             => 'onRequestRefused',
            'meeting_request.canceled'            => 'onRequestCanceled',
            'meeting.canceled'                    => 'onMeetingCanceled',
            'meeting_request.participant.removed' => 'onParticipantRemovedToMeetingRequest',
            'meeting_request.participant.added'   => 'onParticipantAddedToMeetingRequest',
        ];
    }
}
