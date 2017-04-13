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
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationController extends Controller
{
    /**
     * List user notifications
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function listAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet(
            $this->getUser(),
            $eventDomain->getEvent(),
            $request->getLocale()
        );

        if ($sheet === null) {
            throw new NotFoundHttpException('Sheet not found');
        }

        /** @var NotificationListView $notificationListView */
        $notificationListView = $this->get('tactician.commandbus.query')->handle(
            new NotificationViewQuery($sheet)
        );

        return $this->render('EventBundle:Notification:list.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'notifications' => $notificationListView,
        ]);
    }
}
