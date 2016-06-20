<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

use Proximum\Vimeet\Application\Event\Meeting\RequestAcceptedEvent;
use Proximum\Vimeet\Application\Event\Meeting\RequestCanceledEvent;
use Proximum\Vimeet\Application\Event\Meeting\RequestRefusedEvent;
use Proximum\Vimeet\Application\Event\Meeting\RequestSentEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantAddedEvent as MeetingRequestParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantRemovedEvent as MeetingRequestParticipantRemovedEvent;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\Sheet;

class NotificationOnRequestEventSubscriber extends AbstractNotificationEventSubscriber
{
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
        $sheet  = $this->guessSheetThatParticipantHasMeetingWith($event->getParticipant(), $event->getMeetingRequest());
        $locale = $event->getParticipant()->getLocale();

        // Translate message
        $message = $this->translator->trans(
            'notification.meeting_request.participant.added.message',
            [
                '%sheet%' => $this->sheetInfoGuesser->guessSheetName($sheet, $locale),
            ],
            'notifications',
            $locale
        );

        // Send notification
        $this->notificationRepository->add(new Notification(
            $event->getParticipant()->getSheet()->getEvent(),
            $event->getEmitter(),
            $event->getParticipant()->getUser(),
            $event->getDate(),
            'meeting_request.participant.added',
            $message,
            $this->router->generateMeetingRequest($event->getParticipant()->getSheet(), $event->getMeetingRequest())
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
        $sheet  = $this->guessSheetThatParticipantHasMeetingWith($event->getParticipant(), $event->getMeetingRequest());
        $locale = $event->getParticipant()->getLocale();

        // Translate message
        $message = $this->translator->trans(
            'notification.meeting_request.participant.removed.message',
            [
                '%sheet%' => $this->sheetInfoGuesser->guessSheetName($sheet, $locale),
            ],
            'notifications',
            $locale
        );

        // Send notification
        $this->notificationRepository->add(new Notification(
            $event->getParticipant()->getSheet()->getEvent(),
            $event->getEmitter(),
            $event->getParticipant()->getUser(),
            $event->getDate(),
            'meeting_request.participant.removed',
            $message,
            $this->router->generateMeetingRequest($event->getParticipant()->getSheet(), $event->getMeetingRequest())
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
        foreach ($event->getRequest()->getFromSheet()->getUsers() as $user) {
            $locale = $user->getLocale();

            // Translate message
            $message = $this->translator->trans(
                'notification.meeting_request.refused.message',
                [
                    '%to_sheet%' => $this->sheetInfoGuesser->guessSheetName(
                        $event->getRequest()->getToSheet(),
                        $locale
                    ),
                ],
                'notifications',
                $locale
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getRequest()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $user,
                $event->getDate(),
                'meeting_request.refused',
                $message,
                $this->router->generateMeetingRequest($event->getRequest()->getFromSheet(), $event->getRequest())
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
        foreach ($event->getRequest()->getFromSheet()->getUsers() as $user) {
            $locale = $user->getLocale();

            // Translate message
            $message = $this->translator->trans(
                'notification.meeting_request.accepted.message',
                [
                    '%to_sheet%' => $this->sheetInfoGuesser->guessSheetName(
                        $event->getRequest()->getToSheet(),
                        $locale
                    ),
                ],
                'notifications',
                $locale
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getRequest()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $user,
                $event->getDate(),
                'meeting_request.accepted',
                $message,
                $this->router->generateMeetingRequest($event->getRequest()->getFromSheet(), $event->getRequest())
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
        foreach ($event->getRequest()->getFromSheet()->getUsers() as $user) {
            // Don't send notification to user when he is the emitter
            if ($user === $event->getEmitter()) {
                continue;
            }

            $locale = $user->getLocale();

            // Translate message
            $message = $this->translator->trans(
                'notification.meeting_request.canceled.from_message',
                [
                    '%sheet%' => $this->sheetInfoGuesser->guessSheetName($event->getRequest()->getToSheet(), $locale),
                ],
                'notifications',
                $locale
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getRequest()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $user,
                $event->getDate(),
                'meeting_request.canceled',
                $message,
                $this->router->generateMeetingRequest($event->getRequest()->getFromSheet(), $event->getRequest())
            ));
        }

        // To : Get sheet owner and request participants
        foreach ($event->getRequest()->getToSheet()->getUsers() as $user) {
            // Don't send notification to user when he is the emitter
            if ($user === $event->getEmitter()) {
                return;
            }

            $locale = $user->getLocale();

            // Translate message
            $message = $this->translator->trans(
                'notification.meeting_request.canceled.to_message',
                [
                    '%sheet%' => $this->sheetInfoGuesser->guessSheetName($event->getRequest()->getFromSheet(), $locale),
                ],
                'notifications',
                $locale
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getRequest()->getToSheet()->getEvent(),
                $event->getEmitter(),
                $user,
                $event->getDate(),
                'meeting_request.canceled',
                $message,
                $this->router->generateMeetingRequest($event->getRequest()->getToSheet(), $event->getRequest())
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
        $recipient = $event->getRequest()->getToSheet()->getOwner();

        $locale = $recipient->getLocale();

        // Translate message
        $message = $this->translator->trans(
            'notification.meeting_request.receive.message',
            [
                '%from_sheet%' => $this->sheetInfoGuesser->guessSheetName(
                    $event->getRequest()->getFromSheet(),
                    $locale
                ),
            ],
            'notifications',
            $locale
        );

        // Send notification
        $this->notificationRepository->add(new Notification(
            $event->getRequest()->getToSheet()->getEvent(),
            $event->getEmitter(),
            $recipient,
            $event->getDate(),
            'meeting_request.receive',
            $message,
            $this->router->generateMeetingRequest($event->getRequest()->getToSheet(), $event->getRequest())
        ));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // Disable notification on new request, these notification are added
            // in NotificationViewFactory depending on the request state
            //Event::REQUEST_SENT                => 'onRequestSent',
            Events::REQUEST_REFUSED             => 'onRequestRefused',
            Events::REQUEST_CANCELED            => 'onRequestCanceled',
            Events::REQUEST_ACCEPTED            => 'onRequestAccepted',
            Events::REQUEST_PARTICIPANT_ADDED   => 'onParticipantAddedToMeetingRequest',
            Events::REQUEST_PARTICIPANT_REMOVED => 'onParticipantRemovedFromMeetingRequest',
        ];
    }
}
