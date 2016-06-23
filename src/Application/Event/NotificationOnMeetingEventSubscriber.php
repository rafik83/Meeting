<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

use Proximum\Vimeet\Application\Event\Meeting\CanceledEvent;
use Proximum\Vimeet\Application\Event\Meeting\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Meeting\ParticipantRemovedEvent;
use Proximum\Vimeet\Domain\Model\Notification;

class NotificationOnMeetingEventSubscriber extends AbstractNotificationEventSubscriber
{
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
        $sheet  = $this->guessSheetThatParticipantHasMeetingWith($event->getParticipant(), $event->getMeeting());
        $locale = $event->getParticipant()->getLocale();

        // Translate message
        $message = $this->translator->trans(
            'notification.request.participant.added.message',
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
        $sheet  = $this->guessSheetThatParticipantHasMeetingWith($event->getParticipant(), $event->getMeeting());
        $locale = $event->getParticipant()->getLocale();

        // Translate message
        $message = $this->translator->trans(
            'notification.meeting.participant.removed.message',
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
            'meeting.participant.removed',
            $message,
            null
        ));
    }

    /**
     * Notify each from and to participants
     *
     * @param CanceledEvent $event
     */
    public function onMeetingCanceled(CanceledEvent $event)
    {
        // From : Get sheet owner and meeting participants
        foreach ($event->getMeeting()->getFromSheet()->getUsers() as $user) {
            $locale = $user->getLocale();

            // Don't send notification to user when he is the emitter
            if ($user === $event->getEmitter()) {
                return;
            }

            // Translate message
            $message = $this->translator->trans(
                'notification.meeting.canceled.message',
                [
                    '%sheet%' => $this->sheetInfoGuesser->guessSheetName($event->getMeeting()->getToSheet(), $locale),
                ],
                'notifications',
                $locale
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getMeeting()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $user,
                $event->getDate(),
                'metting.canceled',
                $message,
                null
            ));
        }

        // To : Get sheet owner and meeting participants
        foreach ($event->getMeeting()->getToSheet()->getUsers() as $user) {
            // Don't send notification to user when he is the emitter
            if ($user === $event->getEmitter()) {
                return;
            }

            $locale = $user->getLocale();

            // Translate message
            $message = $this->translator->trans(
                'notification.meeting.canceled',
                [
                    '%sheet%' => $this->sheetInfoGuesser->guessSheetName($event->getMeeting()->getFromSheet(), $locale),
                ],
                'notifications',
                $locale
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getMeeting()->getFromSheet()->getEvent(),
                $event->getEmitter(),
                $user,
                $event->getDate(),
                'metting.canceled',
                $message,
                null
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MEETING_PARTICIPANT_ADDED   => 'onParticipantAddedToMeeting',
            Events::MEETING_PARTICIPANT_REMOVED => 'onParticipantRemovedFromMeeting',
            Events::MEETING_CANCELED            => 'onMeetingCanceled',
        ];
    }
}
