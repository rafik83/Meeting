<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Exception\Happening\HappeningException;
use Proximum\Vimeet\Application\Query\Happening\HappeningViewQuery;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProgramController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     *
     * @return RedirectResponse
     */
    public function indexAction(EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        return $this->redirectToRoute('happening_program_day', ['day' => 1]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param int         $day
     *
     * @return Response
     */
    public function dayAction(Request $request, EventDomain $eventDomain, $day)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        if ($day !== '1') {
            throw $this->createNotFoundException('The program is not available on this day');
        }

        try {
            $happeningListView = $this->get('tactician.commandbus.query')->handle(
                new HappeningViewQuery(
                    $eventDomain->getEvent(),
                    $request->getLocale(),
                    $day
                )
            );
        } catch (HappeningException $exception) {
            return $this->redirectToRoute('event_sheet');
        }

        return $this->render('EventBundle:Program:day.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'happeningList' => $happeningListView,
        ]);
    }
}
