<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function indexAction(EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        return $this->render('EventBundle:Agenda:index.html.twig', [
            'event' => $eventDomain->getEvent(),
        ]);
    }
}
