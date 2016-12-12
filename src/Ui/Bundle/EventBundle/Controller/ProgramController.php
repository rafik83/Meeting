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

        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $locale);

        try {
            /** @var ProgramView $program */
            $program = $this->get('tactician.commandbus.query')->handle(
                new ProgramViewQuery(
                    $eventDomain->getEvent(),
                    $request->getLocale(),
                    null
                )
            );
        } catch (HappeningException $exception) {
            return $this->redirectToRoute('event_sheet');
        }

        $happenings = [];

        foreach ($program->days as $day) {
            foreach ($day->happenings as $happening) {
                $happenings[] = $happening->getId();
            }
        }

        $participations = $this
            ->get('vimeet_infrastructure.repository.happening_participation_repository')
            ->getParticipationsForSheet($sheet, $happenings);

        $happeningParticipations = [];

        foreach ($participations as $participation) {
            /** @var HappeningParticipation $participation */
            $happeningParticipations[$participation->getHappening()->getId()][] = $participation->getParticipant();
        }

        return $this->render('EventBundle:Program:index.html.twig', [
            'event'   => $event,
            'sheet'   => $sheet,
            'program' => $program,
            'happeningParticipations' => $happeningParticipations,
        ]);
    }
}
