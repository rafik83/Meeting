<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Event\Meeting\CanceledEvent;
use Proximum\Vimeet\Application\Event\Meeting\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Meeting\RequestAcceptedEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\MessageEvent;
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
     * @var RouterInterface
     */
    private $router;

    /**
     * NotificationEventListener constructor.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     * @param SheetInfoGuesser                $sheetInfoGuesser
     * @param TranslatorInterface             $translator
     * @param RouterInterface                 $router
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        TranslatorInterface $translator,
        RouterInterface $router
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->translator             = $translator;
        $this->router                 = $router;
    }

    /**
     * Notify added participant
     *
     * @param ParticipantAddedEvent $event
     */
    public function onParticipantAddedToMeeting(ParticipantAddedEvent $event)
    {
        // Don't send notification to user when he is the emitter
        if ($event->getParticipant()->getUser() === $event->getEmitter()) {
            return;
        }

        // Guess the sheet the participant has meeting with
        if ($event->getParticipant()->getSheet() === $event->getMeeting()->getFromSheet()) {
            $sheet = $event->getMeeting()->getToSheet();
        } elseif ($event->getParticipant()->getSheet() === $event->getMeeting()->getToSheet()) {
            $sheet = $event->getMeeting()->getFromSheet();
        } else {
            throw new \RuntimeException('Unable to dertimine the sheet the participant has meeting with.');
        }

        // Translate message
        $message = $this->translator->trans(
            'notification.request.participant.added.message',
            [
                '%sheet%' => $this->sheetInfoGuesser->guessSheetInfo($sheet),
            ],
            'notifications',
            $event->getParticipant()->getUser()->getLocale()
        );

        // Send notification
        $this->notificationRepository->add(new Notification(
            $event->getParticipant()->getSheet()->getEvent(),
            $event->getEmitter(),
            $event->getParticipant()->getUser(),
            $event->getDate(),
            'notification.meeting.participant.added.message',
            $message,
            null
        ));
    }

    /**
     * Notify removed participant
     *
     * @param ParticipantRemovedEvent $event
     */
    public function onParticipantRemovedFromMeeting(ParticipantRemovedEvent $event)
    {
        // Don't send notification to user when he is the emitter
        if ($event->getParticipant()->getUser() === $event->getEmitter()) {
            return;
        }

        // Guess the sheet the participant has meeting with
        if ($event->getParticipant()->getSheet() === $event->getMeeting()->getFromSheet()) {
            $sheet = $event->getMeeting()->getToSheet();
        } elseif ($event->getParticipant()->getSheet() === $event->getMeeting()->getToSheet()) {
            $sheet = $event->getMeeting()->getFromSheet();
        } else {
            throw new \RuntimeException('Unable to dertimine the sheet the participant has meeting with.');
        }

        // Translate message
        $message = $this->translator->trans(
            'notification.meeting.participant.removed.message',
            [
                '%sheet%' => $this->sheetInfoGuesser->guessSheetInfo($sheet),
            ],
            'notifications',
            $event->getParticipant()->getUser()->getLocale()
        );

        // Send notification
        $this->notificationRepository->add(new Notification(
            $event->getParticipant()->getSheet()->getEvent(),
            $event->getEmitter(),
            $event->getParticipant()->getUser(),
            $event->getDate(),
            'meeting.participant.removed',
            $message,
            null
        ));
    }

    /**
     * Notify added participant
     *
     * @param MeetingRequestParticipantAddedEvent $event
     */
    public function onParticipantAddedToMeetingRequest(MeetingRequestParticipantAddedEvent $event)
    {
        // Don't send notification to user when he is the emitter
        if ($event->getParticipant()->getUser() === $event->getEmitter()) {
            return;
        }

        // Guess the sheet the participant has meeting with
        if ($event->getParticipant()->getSheet() === $event->getMeetingRequest()->getFromSheet()) {
            $sheet = $event->getMeetingRequest()->getToSheet();
        } elseif ($event->getParticipant()->getSheet() === $event->getMeetingRequest()->getToSheet()) {
            $sheet = $event->getMeetingRequest()->getFromSheet();
        } else {
            throw new \RuntimeException('Unable to dertimine the sheet the participant has meeting with.');
        }

        // Translate message
        $message = $this->translator->trans(
            'notification.meeting_request.participant.added.message',
            [
                '%sheet%' => $this->sheetInfoGuesser->guessSheetInfo($sheet),
            ],
            'notifications',
            $event->getParticipant()->getUser()->getLocale()
        );

        // Send notification
        $this->notificationRepository->add(new Notification(
            $event->getParticipant()->getSheet()->getEvent(),
            $event->getEmitter(),
            $event->getParticipant()->getUser(),
            $event->getDate(),
            'meeting_request.participant.added',
            $message,
            $this->router->generateMeetingRequest($event->getMeetingRequest())
        ));
    }

    /**
     * Notify removed participant
     *
     * @param MeetingRequestParticipantRemovedEvent $event
     */
    public function onParticipantRemovedFromMeetingRequest(MeetingRequestParticipantRemovedEvent $event)
    {
        // Don't send notification to user when he is the emitter
        if ($event->getParticipant()->getUser() === $event->getEmitter()) {
            return;
        }

        // Guess the sheet the participant has meeting with
        if ($event->getParticipant()->getSheet() === $event->getMeetingRequest()->getFromSheet()) {
            $sheet = $event->getMeetingRequest()->getToSheet();
        } elseif ($event->getParticipant()->getSheet() === $event->getMeetingRequest()->getToSheet()) {
            $sheet = $event->getMeetingRequest()->getFromSheet();
        } else {
            throw new \RuntimeException('Unable to dertimine the sheet the participant has meeting with.');
        }

        // Translate message
        $message = $this->translator->trans(
            'notification.meeting_request.participant.removed.message',
            [
                '%sheet%' => $this->sheetInfoGuesser->guessSheetInfo($sheet),
            ],
            'notifications',
            $event->getParticipant()->getUser()->getLocale()
        );

        // Send notification
        $this->notificationRepository->add(new Notification(
            $event->getParticipant()->getSheet()->getEvent(),
            $event->getEmitter(),
            $event->getParticipant()->getUser(),
            $event->getDate(),
            'meeting_request.participant.removed',
            $message,
            $this->router->generateMeetingRequest($event->getRequest())
        ));
    }

    /**
     * Notify from participant and the from sheet owner
     *
     * @param RequestRefusedEvent $event
     */
    public function onRequestRefused(RequestRefusedEvent $event)
    {
        // From : Get sheet owner and request participants
        $fromSheetOwner   = $event->getRequest()->getFromSheet()->getOwner();
        $fromParticipants = $event->getRequest()->getFromParticipants()->toArray();

        if (!in_array($fromSheetOwner, $fromParticipants)) {
            array_push($fromParticipants, $fromSheetOwner);
        }

        foreach ($fromParticipants as $participant) {
            // Translate message
            $message = $this->translator->trans(
                'notification.meeting_request.refused.message',
                [
                    '%to_sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getToSheet()),
                ],
                'notifications',
                $participant->getUser()->getLocale()
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getRequest()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'meeting_request.refused',
                $message,
                $this->router->generateMeetingRequest($event->getRequest())
            ));
        }
    }

    /**
     * Notify the request has been accepted
     *
     * @param RequestAcceptedEvent $event
     */
    public function onRequestAccepted(RequestAcceptedEvent $event)
    {
        // From : Get sheet owner and request participants
        $fromSheetOwner   = $event->getRequest()->getFromSheet()->getOwner();
        $fromParticipants = $event->getRequest()->getFromParticipants()->toArray();

        if (!in_array($fromSheetOwner, $fromParticipants)) {
            array_push($fromParticipants, $fromSheetOwner);
        }

        foreach ($fromParticipants as $participant) {
            // Translate message
            $message = $this->translator->trans(
                'notification.meeting_request.accepted.message',
                [
                    '%to_sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getToSheet()),
                ],
                'notifications',
                $participant->getUser()->getLocale()
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getRequest()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'meeting_request.accepted',
                $message,
                $this->router->generateMeetingRequest($event->getRequest())
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
        // From : Get owner and request participants
        $fromSheetOwner   = $event->getRequest()->getFromSheet()->getOwner();
        $fromParticipants = $event->getRequest()->getFromParticipants()->toArray();

        if (!in_array($fromSheetOwner, $fromParticipants)) {
            array_push($fromParticipants, $fromSheetOwner);
        }

        foreach ($fromParticipants as $participant) {

            // Don't send notification to user when he is the emitter
            if ($participant->getUser() === $event->getEmitter()) {
                continue;
            }

            // Translate message
            $message = $this->translator->trans(
                'notification.meeting_request.canceled.from_message',
                [
                    '%sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getToSheet()),
                ],
                'notifications',
                $participant->getUser()->getLocale()
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getRequest()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'meeting_request.canceled',
                $message,
                $this->router->generateMeetingRequest($event->getRequest())
            ));
        }

        // To : Get sheet owner and request participants
        $toSheetOwner   = $event->getRequest()->getToSheet()->getOwner();
        $toParticipants = $event->getRequest()->getToParticipants()->toArray();

        if (!in_array($toSheetOwner, $toParticipants)) {
            array_push($toParticipants, $toSheetOwner);
        }

        foreach ($toParticipants as $participant) {

            // Don't send notification to user when he is the emitter
            if ($participant->getUser() === $event->getEmitter()) {
                return;
            }

            // Translate message
            $message = $this->translator->trans(
                'notification.meeting_request.canceled.to_message',
                [
                    '%sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getFromSheet()),
                ],
                'notifications',
                $participant->getUser()->getLocale()
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getRequest()->getToSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'meeting_request.canceled',
                $message,
                $this->router->generateMeetingRequest($event->getRequest())
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
        // From : Get sheet owner and meeting participants
        $fromSheetOwner   = $event->getMeeting()->getFromSheet()->getOwner();
        $fromParticipants = $event->getMeeting()->getFromParticipants()->toArray();

        if (!in_array($fromSheetOwner, $fromParticipants)) {
            array_push($fromParticipants, $fromSheetOwner);
        }

        foreach ($fromParticipants as $participant) {

            // Don't send notification to user when he is the emitter
            if ($participant->getUser() === $event->getEmitter()) {
                return;
            }

            // Translate message
            $message = $this->translator->trans(
                'notification.meeting.canceled.message',
                [
                    '%sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getToSheet()),
                ],
                'notifications',
                $participant->getUser()->getLocale()
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getMeeting()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'metting.canceled',
                $message,
                null
            ));
        }

        // To : Get sheet owner and meeting participants
        $toSheetOwner   = $event->getRequest()->getFromSheet()->getOwner();
        $toParticipants = $event->getRequest()->getFromParticipants()->toArray();

        if (!in_array($toSheetOwner, $toParticipants)) {
            array_push($toParticipants, $toSheetOwner);
        }

        foreach ($toParticipants as $participant) {

            // Don't send notification to user when he is the emitter
            if ($participant->getUser() === $event->getEmitter()) {
                return;
            }

            // Translate message
            $message = $this->translator->trans(
                'notification.meeting.canceled',
                [
                    '%sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getFromSheet()),
                ],
                'notifications',
                $participant->getUser()->getLocale()
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getMeeting()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $participant->getUser(),
                $event->getDate(),
                'metting.canceled',
                $message,
                null
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
        // Get sheet owner
        $recipient = $event->getRequest()->getToSheet()->getOwner()->getUser();

        // Translate message
        $message = $this->translator->trans(
            'notification.meeting_request.receive.message',
            [
                '%from_sheet%' => $this->sheetInfoGuesser->guessSheetInfo($event->getRequest()->getFromSheet()),
            ],
            'notifications',
            $recipient->getLocale()
        );

        // Send notification
        $this->notificationRepository->add(new Notification(
            $event->getRequest()->getFromSheet()->getEvent(),
            $event->getEmitter(),
            $recipient,
            $event->getDate(),
            'meeting_request.receive',
            $message,
            $this->router->generateMeetingRequest($event->getRequest())
        ));
    }

    /**
     * @param MessageEvent $event
     */
    public function onMessage(MessageEvent $event)
    {

    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'meeting.participant.added'           => 'onParticipantAddedToMeeting',
            'meeting.participant.removed'         => 'onParticipantRemovedFromMeeting',
            // Disable notification on new request, these notification are added
            // in NotificationViewFactory depending on the request state
            //'meeting_request.sent'                => 'onRequestSent',
            'meeting_request.refused'             => 'onRequestRefused',
            'meeting_request.canceled'            => 'onRequestCanceled',
            'meeting_request.accepted'            => 'onRequestAccepted',
            'meeting.canceled'                    => 'onMeetingCanceled',
            'meeting_request.participant.removed' => 'onParticipantRemovedFromMeetingRequest',
            'meeting_request.participant.added'   => 'onParticipantAddedToMeetingRequest',
            'meeting_request.message'             => 'onMessage',
        ];
    }
}
