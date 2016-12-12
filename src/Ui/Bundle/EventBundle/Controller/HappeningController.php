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
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent());

        $participant = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantForUserAndSheet($this->getUser(), $sheet);

        if (null === $participant) {
            return new JsonResponse(['error' => 'Participant not found'], 404);
        }

        $participate = new Participate($happening, [$participant]);
        $this->get('tactician.commandbus')->handle($participate);

        return new JsonResponse([]);
    }
}
