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

        $unreadNotifications = $this->get('vimeet_infrastructure.repository.notification_repository')
            ->getUnreadByEventAndUser($eventView->id, $this->getUser());

        return $this->render('VimeetAppBundle:Event/Notification:list.html.twig', [
            'eventView'           => $eventView,
            'unreadNotifications' => $unreadNotifications,
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

        $unreadNotifications = $this->get('vimeet_infrastructure.repository.notification_repository')
            ->getUnreadByEventAndUser($eventView->id, $this->getUser());

        return $this->render('VimeetAppBundle:Event/Notification:unreadNumber.html.twig', [
            'unreadNumber' => count($unreadNotifications),
        ]);
    }
}
