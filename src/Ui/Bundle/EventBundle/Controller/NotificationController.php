<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Notification\NotificationViewQuery;
use Proximum\Vimeet\Application\View\Notification\NotificationListView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function listAction(EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        /** @var NotificationListView $notificationListView */
        $notificationListView = $this->get('tactician.commandbus.query')->handle(
            new NotificationViewQuery($sheet)
        );

        return $this->render('EventBundle:Notification:list.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'sheet'         => $sheet,
            'notifications' => $notificationListView,
        ]);
    }
}
