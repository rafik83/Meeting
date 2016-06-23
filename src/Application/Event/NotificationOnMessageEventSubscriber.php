<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

use Proximum\Vimeet\Application\Event\MeetingRequest\MessageEvent;
use Proximum\Vimeet\Domain\Model\Notification;

class NotificationOnMessageEventSubscriber extends AbstractNotificationEventSubscriber
{
    /**
     * @param MessageEvent $event
     */
    public function onMessage(MessageEvent $event)
    {
        // Get sheet owner and subject participants
        foreach ($event->getMessage()->getTo()->getUsers() as $user) {
            $locale = $user->getLocale();

            // Translate message
            $message = $this->translator->trans(
                'notification.message.received.message',
                [
                    '%from_sheet%' => $this->sheetInfoGuesser->guessSheetName($event->getMessage()->getFrom(), $locale),
                ],
                'notifications',
                $locale
            );

            // Send notification
            $this->notificationRepository->add(new Notification(
                $event->getMessage()->getFrom()->getEvent(),
                $event->getEmitter(),
                $user,
                $event->getMessage()->getCreatedAt(),
                'message.received',
                $message,
                $this->router->generateSubject($event->getMessage()->getTo(), $event->getMessage()->getSubject())
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST_UPDATE_MESSAGE => 'onMessage',
            EVents::MEETING_UPDATE_MESSAGE => 'onMessage',
        ];
    }
}
