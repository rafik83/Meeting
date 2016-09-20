<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
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

        $users = $this
            ->get('vimeet_infrastructure.repository.user_repository')
            ->paginate(
                $request->query->get('page', 1),
                20,
                $event->getId()
            );

        foreach ($users->results as $user) {
            
        }
        return $this->render('AdminBundle:User:list.html.twig', [
            'event' => $event,
            'users' => $users
        ]);
    }

}
