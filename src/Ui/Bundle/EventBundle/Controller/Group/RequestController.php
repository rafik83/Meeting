<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestController extends Controller
{
    const PAGINATE_REQUEST_LIMIT = 50;

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Group       $sheetGroup
     *
     * @return Response
     */
    public function listAction(Request $request, EventDomain $eventDomain, Group $sheetGroup)
    {
        return $this->render('EventBundle:Sheet/Group/Request:index.html.twig', [
            'event' => $eventDomain->getEvent(),
        ]);
    }
}
