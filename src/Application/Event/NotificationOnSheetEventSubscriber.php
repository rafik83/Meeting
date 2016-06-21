<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Proximum\Vimeet\Domain\Model\Notification;

class NotificationOnSheetEventSubscriber extends AbstractNotificationEventSubscriber
{

    /**
     * Notify sheet owner when the sheet is validated
     *
     * @param SheetValidatedEvent $event
     */
    public function onSheetValidated(SheetValidatedEvent $event)
    {
        // Get owner
        $owner = $event->getSheet()->getOwner();

        // Translated message
        $message = $this->translator->trans(
            'notification.sheet.validated.message',
            [],
            'notifications',
            $owner->getLocale()
        );

        // Send notification
        $this->notificationRepository->add(new Notification(
            $event->getSheet()->getEvent(),
            $owner,
            $owner,
            new \DateTime(),
            'sheet.validated',
            $message,
            $this->router->generateSheet($event->getSheet())
        ));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_VALIDATED => 'onSheetValidated',
        ];
    }
}
