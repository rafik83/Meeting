<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends BaseController
{
    /**
     * List user notifications
     *
     * @param EventView $eventView
     *
     * @return Response
     */
    public function listAction(EventView $eventView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $notifications = $this
            ->get('notification.notification_view_factory')
            ->getNotificationsByEventAndUser($eventView->id, $this->getUser());

        return $this->render('VimeetAppBundle:Event/Notification:list.html.twig', [
            'eventView'     => $eventView,
            'notifications' => $notifications,
        ]);
    }

    /**
     * @param EventView $eventView
     *
     * @return Response
     */
    public function unreadNumberAction(EventView $eventView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $count = $this
            ->get('notification.notification_view_factory')
            ->countUnreadNotificationByEventAndUser($eventView->id, $this->getUser());

        return $this->render('VimeetAppBundle:Event/Notification:unreadNumber.html.twig', [
            'unreadNumber' => $count,
        ]);
    }

    /**
     * Mark the notification as read and redirect to the embedded url
     *
     * @param Notification $notification
     *
     * @return RedirectResponse
     */
    public function readAction(Notification $notification)
    {
        if ($notification->getRecipient() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not allowed to read this notification.');
        }

        $this->get('vimeet_infrastructure.repository.notification_repository')->set($notification->markAsRead());

        return $this->redirect($notification->getUrl());
    }
}
