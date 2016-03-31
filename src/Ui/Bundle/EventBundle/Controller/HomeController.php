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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * Event home.
     *
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return Response
     */
    public function indexAction(Request $request, EventView $eventView)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $sheets = $this
                ->get('vimeet_infrastructure.repository.sheet_repository')
                ->getSheetViewsByUserAndEvent($this->getUser()->getId(), $eventView->id, $request->getLocale());
        } else {
            $sheets = [];
        }

        $typeViews = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypeViewsByEvent($eventView->id, $eventView->locale);

        return $this->render('VimeetAppBundle:Event/Home:index.html.twig', [
            'eventView' => $eventView,
            'types'     => $typeViews,
            'sheets'    => $sheets,
        ]);
    }
}
