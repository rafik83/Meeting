<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Unavailability\Create;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Exception\Unavailability\NoParticipantSelectedException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsSelectedWithMeetingOrHappeningException;
use Proximum\Vimeet\Application\Exception\Unavailability\TimeOutOfRangeException;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability\CreateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UnavailabilityController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function createAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        $event = $eventDomain->getEvent();
        $user  = $this->getUser();

        try {
            $sheet = $this
                ->get('sheet.sheet_guesser')
                ->getUserSheet($this->getUser(), $event, $request->getLocale());
        } catch (SheetNotFoundException $exception) {
            throw $this->createNotFoundException('Sheet not found');
        }

        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($user, $sheet);

        $create = new Create($event, $sheet, $user, $request->getLocale());
        $form   = $this->createForm(CreateType::class, $create, [
            'action'                 => $this->generateUrl('event_unavailability_create'),
            'isUserAloneParticipant' => $isUserAloneParticipant,
            'event'                  => $event,
            'locale'                 => $request->getLocale(),
            'sheet'                  => $sheet,
        ]);

        // If the page is called by an ajax request, only show the form
        if ($request->isXmlHttpRequest()) {
            return $this->render('EventBundle:Unavailability:create-form.html.twig', [
                'form_unavailability' => $form->createView(),
            ]);
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($create);

                return $this->redirectToRoute('event_agenda');
            } catch (NoParticipantSelectedException $exception) {
                $form->get('participants')->addError(
                    new FormError(
                        $this->get('translator')->trans(
                            'validators.unavailability.participantsNotSelected',
                            [],
                            'validators'
                        )
                    )
                );
            } catch (ParticipantsSelectedWithMeetingOrHappeningException $exception) {
                $form->get('participants')->addError(
                    new FormError(
                        $this->get('translator')->transChoice(
                            'validators.unavailability.participantsWithConflict',
                            $exception->getNumberOfConflict(),
                            ['participants' => $exception->getListOfParticipantsName()],
                            'validators'
                        )
                    )
                );
            } catch (TimeOutOfRangeException $exception) {
                if ($exception->isOutOfRangeAtBeginOfDay()) {
                    $form->get('time')->get('begin')->addError(
                        new FormError(
                            $this->get('translator')->trans(
                                'validators.unavailability.timeOutOfRange.begin',
                                ['day' => $exception->day->getDay()->format('D M Y')],
                                 'validators'
                            )
                        )
                    );
                } elseif ($exception->isOutOfRangeAtEndOfDay()) {
                    $form->get('time')->get('end')->addError(
                        new FormError(
                            $this->get('translator')->trans(
                                'validators.unavailability.timeOutOfRange.end',
                                ['day' => $exception->day->getDay()->format('D M Y')],
                                'validators'
                            )
                        )
                    );
                }
            }
        }

        /** @var AgendaView $agenda */
        $agenda = $this
            ->get('tactician.commandbus.query')
            ->handle(
                new AgendaViewQuery($event, $user, $request->getLocale())
            );

        return $this->render('EventBundle:Unavailability:create.html.twig', [
            'event'                  => $event,
            'agenda'                 => $agenda,
            'isUserAloneParticipant' => $isUserAloneParticipant,
            'form_unavailability'    => $form->createView()
        ]);
    }
}
