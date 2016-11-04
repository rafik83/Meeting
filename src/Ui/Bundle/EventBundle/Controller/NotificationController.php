<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Notification\NotificationViewQuery;
use Proximum\Vimeet\Application\View\Notification\NotificationListView;
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    /**
     * List user notifications
     *
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function listAction(EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var NotificationListView $notificationListView */
        $notificationListView = $this->get('tactician.commandbus.query')->handle(
            new NotificationViewQuery($sheet)
        );

        return $this->render('EventBundle:Notification:list.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'notifications' => $notificationListView,
        ]);
    }

    /**
     * @deprecated
     *
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function unreadNumberAction(EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $count = $this
            ->get('notification.notification_view_factory')
            ->countUnreadNotificationByEventAndUser($eventDomain->getEvent(), $this->getUser());

        return $this->render('EventBundle:Notification:unreadNumber.html.twig', [
            'unreadNumber' => $count,
        ]);
    }

    /**
     * Mark the notification as read and redirect to the embedded url
     *
     * @deprecated
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
