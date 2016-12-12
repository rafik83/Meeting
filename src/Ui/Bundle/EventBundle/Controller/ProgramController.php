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
use Proximum\Vimeet\Application\Query\Happening\ProgramViewQuery;
use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgramController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function indexAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        $event  = $eventDomain->getEvent();
        $locale = $request->getLocale();
        $user   = $this->getUser();

        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet($user, $event, $locale);

        if ($sheet === null) {
            throw $this->createNotFoundException(sprintf('This user %s has no sheet', $user->getId()));
        }

        try {
            /** @var ProgramView $program */
            $program = $this->get('tactician.commandbus.query')->handle(
                new ProgramViewQuery(
                    $eventDomain->getEvent(),
                    $sheet,
                    $user,
                    $request->getLocale(),
                    null
                )
            );
        } catch (HappeningException $exception) {
            return $this->redirectToRoute('event_sheet');
        }

        return $this->render('EventBundle:Program:index.html.twig', [
            'event'   => $event,
            'sheet'   => $sheet,
            'program' => $program,
        ]);
    }
}
