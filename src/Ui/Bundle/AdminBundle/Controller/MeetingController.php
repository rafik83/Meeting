<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteAll;
use Proximum\Vimeet\Application\Exception\Meeting\NotAllowedToDeleteAllMeetingsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MeetingController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $meetings = $this
            ->get('vimeet_infrastructure.repository.meeting_repository')
            ->getByEvent(
                $event,
                $request->query->getInt('page', 1),
                20,
                $event->getAvailableLocale($request->getLocale())
            );

        return $this->render('AdminBundle:Meeting:list.html.twig', [
            'event'    => $event,
            'meetings' => $meetings,
        ]);
    }

    /**
     * @param Event $event
     *
     * @return RedirectResponse
     */
    public function deleteAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if (!$this->getUser() instanceof Admin || !$this->getUser()->isSuperAdmin()) {
            throw $this->createNotFoundException('Action not allowed for this user');
        }

        try {
            $this->get('tactician.commandbus')->handle(new DeleteAll($event));
        } catch (NotAllowedToDeleteAllMeetingsException $exception) {
            $this->addFlash('error', 'flash.admin.meeting.notAllowedToDeleteAllMeetingsException');
        }

        return $this->redirectToRoute('admin_meeting_list', ['event' => $event->getId()]);
    }
}
