<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends BaseController
{
    /**
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
}
