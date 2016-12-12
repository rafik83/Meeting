<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class HappeningController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Happening   $happening
     *
     * @return JsonResponse
     */
    public function participateAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Happening $happening)
    {
        $event = $eventDomain->getEvent();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $event);

        if ($happening->getEvent() !== $event || $sheet->getEvent() !== $event) {
            throw $this->createNotFoundException('Happening or sheet not in this event');
        }

        $participant = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantForUserAndSheet($this->getUser(), $sheet);

        if (null === $participant) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => $this->get('translator')->trans('happening.participate.participantNoFound'),
                ]
            );
        }

        $participate = new Participate($happening, [$participant]);

        try {
            $this->get('tactician.commandbus')->handle($participate);
        } catch (ParticipantNotAvailableException $participantNotAvailableException) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => $this->get('translator')->trans('happening.participate.youAreNotAvailable'),
                ]
            );
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
