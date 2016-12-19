<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     * @param Request     $request
     *
     * @return Response
     */
    public function indexAction(EventDomain $eventDomain, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        try {
            /** @var AgendaView $agenda */
            $agenda = $this
                ->get('tactician.commandbus.query')
                ->handle(
                    new AgendaViewQuery($eventDomain->getEvent(), $this->getUser(), $request->getLocale())
                );
        } catch (SheetNotFoundException $exception) {
            throw $this->createNotFoundException('Sheet not found');
        } catch (\Exception $exception) {
            throw $this->createNotFoundException('Can not display this page');
        }

        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($this->getUser(), $agenda->sheet);

        return $this->render('EventBundle:Agenda:index.html.twig', [
            'event'                  => $eventDomain->getEvent(),
            'agenda'                 => $agenda,
            'isUserAloneParticipant' => $isUserAloneParticipant,
        ]);
    }
}
