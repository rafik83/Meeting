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
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Happening\ParticipateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
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
        $event = $eventDomain->getEvent();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $event);
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if ($happening->getEvent() !== $event || $sheet->getEvent() !== $event) {
            throw $this->createNotFoundException('Happening or sheet not in this event');
        }

        $participants           = $sheet->getParticipants()->toArray();
        $isUserAloneParticipant = $this->isUserAloneParticipant($sheet);

        // Case : current user is not available for this happening, do not show modal
        if (true === $isUserAloneParticipant) {
            $availableParticipants = $this
                ->get('vimeet_infrastructure.repository.participant_repository')
                ->getAvailableParticipants(
                    $participants,
                    $happening->getBegin(),
                    $happening->getEnd()
                );

            if (0 === count($availableParticipants)) {
                return $this->createJsonResponseWithError('happening.participate.youAreNotAvailable');
            }
        }

        $participate = new Participate($happening, $participants);

        // Case : one participant is current user and no question
        if (true === $isUserAloneParticipant && false === $happening->isQuestionAllowed()) {
            try {
                $this->get('tactician.commandbus')->handle($participate);
            } catch (ParticipantNotAvailableException $participantNotAvailableException) {
                return $this->createJsonResponseWithError('happening.participate.youAreNotAvailable');
            }

            return new JsonResponse(['status' => 'ok']);
        }

        // Create Participate form
        $participateForm = $this->createForm(ParticipateType::class, $participate, [
            'action'    => $this->generateUrl(
                'event_sheet_happening_participate',
                [
                    'sheet'     => $sheet->getId(),
                    'happening' => $happening->getId(),
                ]
            ),
            'method'    => 'POST',
            'happening' => $happening,
        ]);

        if ($participateForm->handleRequest($request)->isSubmitted() && $participateForm->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($participate);

                return new JsonResponse(['status' => 'ok']);
            } catch (ParticipantNotAvailableException $participantNotAvailableException) {
                $participateForm->addError(new FormError($this->get('translator')->trans(
                    true === $isUserAloneParticipant
                    ? 'happening.participate.youAreNotAvailable'
                    : 'happening.participate.participantNotAvailable'
                )));
            }
        }

        $template = 'EventBundle:Program/Partials:participate-modal.html.twig';

        return new JsonResponse(
            [
                'status' => 'show-form',
                'html'   => $this->renderView($template, [
                    'title' => $happening->getTitle($request->getLocale()),
                    'picto' => $happening->getCategory()->getPicto(),
                    'form'  => $participateForm->createView(),
                ]),
            ]
        );
    }

    /**
     * @param string $errorKey
     *
     * @return JsonResponse
     */
    private function createJsonResponseWithError($errorKey)
    {
        return new JsonResponse(
            [
                'status'  => 'error',
                'message' => $this->get('translator')->trans($errorKey),
            ]
        );
    }

    /**
     * There is one participant in this sheet and this participant is the current logged user
     *
     * @param Sheet $sheet
     *
     * @return bool
     */
    private function isUserAloneParticipant(Sheet $sheet)
    {
        $participants = $sheet->getParticipants();

        if (1 === count($participants)) {
            $participant = $participants->first();

            if ($participant->getUser() === $this->getUser()) {
                return true;
            }
        }

        return false;
    }
}
