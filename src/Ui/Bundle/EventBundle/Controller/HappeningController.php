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
use Proximum\Vimeet\Application\Exception\Happening\NotEnoughtRemainingParticipationsException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantRequiredException;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
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

        $selectedParticipants = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantsForHappening($sheet, $happening);

        $previousQuestion = null;

        if (count($selectedParticipants) > 0 && $happening->isQuestionAllowed()) {
            $question = $this
                ->get('vimeet_infrastructure.repository.happening_question_repository')
                ->getByUserAndHappening($this->getUser(), $happening);

            if (null !== $question) {
                $previousQuestion = $question->getContent();
            }
        }

        $participants           = $sheet->getParticipants()->toArray();
        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($this->getUser(), $sheet);

        $availableParticipants = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getAvailableParticipantsForHappening($participants, $happening);

        // Case : user alone in sheet
        if (true === $isUserAloneParticipant) {
            // and it is new participation, he is selected directly
            if (0 === count($selectedParticipants)) {
                $selectedParticipants = $participants;

                // Case : current user is not available for this happening, do not show modal
                if (true === $isUserAloneParticipant && 0 === count($availableParticipants)) {
                    return $this->createJsonResponseWithError('happening.participate.youAreNotAvailable');
                }
            } else {
                // Unselect current user
                $selectedParticipants = [];
            }
        }

        // Case : one participant is current user and no question so no modal
        if (true === $isUserAloneParticipant && false === $happening->isQuestionAllowed()) {
            try {
                $participate = new Participate($happening, $sheet, $this->getUser(), $selectedParticipants);
                $this->get('tactician.commandbus')->handle($participate);
            } catch (ParticipantNotAvailableException $participantNotAvailableException) {
                return $this->createJsonResponseWithError('happening.participate.youAreNotAvailable');
            } catch (NotEnoughtRemainingParticipationsException $notEnoughtRemainingParticipationsException) {
                $remainingParticipations = $notEnoughtRemainingParticipationsException->getRemainingParticipations();

                return $this->createJsonResponseWithError(
                    'happening.participate.notEnoughtRemainingParticipations',
                    ['%remaining%' => $remainingParticipations],
                    $remainingParticipations
                );
            }

            return new JsonResponse(['status' => 'ok']);
        }

        $participate = new Participate(
            $happening,
            $sheet,
            $this->getUser(),
            $selectedParticipants,
            $previousQuestion
        );

        // Create Participate form
        $participateForm = $this->createForm(ParticipateType::class, $participate, [
            'action'                => $this->generateUrl(
                'event_sheet_happening_participate',
                [
                    'sheet'     => $sheet->getId(),
                    'happening' => $happening->getId(),
                ]
            ),
            'method'                => 'POST',
            'happening'             => $happening,
            'participants'          => $participants,
            'isParticipantsEnabled' => false === $isUserAloneParticipant,
            'locale'                => $request->getLocale(),
        ]);

        if ($participateForm->handleRequest($request)->isSubmitted() && $participateForm->isValid()) {
            $formOrParticipantsField = true === $participateForm->has('participants')
                ? $participateForm->get('participants')
                : $participateForm;

            try {
                $this->get('tactician.commandbus')->handle($participate);

                return new JsonResponse(['status' => 'ok']);
            } catch (ParticipantNotAvailableException $participantNotAvailableException) {
                $formOrParticipantsField->addError(new FormError($this->get('translator')->trans(
                    true === $isUserAloneParticipant
                    ? 'happening.participate.youAreNotAvailable'
                    : 'happening.participate.participantNotAvailable'
                )));
            } catch (ParticipantRequiredException $participantRequiredException) {
                $formOrParticipantsField->addError(new FormError($this->get('translator')->trans(
                    'happening.participate.noParticipantSelected'
                )));
            } catch (NotEnoughtRemainingParticipationsException $notEnoughtRemainingParticipationsException) {
                $remainingParticipations = $notEnoughtRemainingParticipationsException->getRemainingParticipations();
                $formOrParticipantsField->addError(new FormError($this->get('translator')->transChoice(
                    'happening.participate.notEnoughtRemainingParticipations',
                    $remainingParticipations,
                    ['%remaining%' => $remainingParticipations]
                )));
            }
        }

        $unavailableParticipants = [];

        foreach ($participants as $key => $participant) {
            if (false === in_array($participant, $availableParticipants)) {
                $unavailableParticipants[$key] = $participant;
            }
        }

        $template = 'EventBundle:Program/Partials:participate-modal.html.twig';

        return new JsonResponse(
            [
                'status' => 'show-form',
                'html'   => $this->renderView($template, [
                    'title'                   => $happening->getTitle($request->getLocale()),
                    'picto'                   => $happening->getCategory()->getPicto(),
                    'form'                    => $participateForm->createView(),
                    'unavailableParticipants' => $unavailableParticipants,
                    'noAvailableParticipants' => 0 === count($availableParticipants),
                ]),
            ]
        );
    }

    /**
     * @param string   $errorKey
     * @param array    $parameters
     * @param int|null $number
     *
     * @return JsonResponse
     */
    private function createJsonResponseWithError($errorKey, $parameters = [], $number = null)
    {
        $translator = $this->get('translator');

        return new JsonResponse(
            [
                'status'  => 'error',
                'message' => null === $number
                    ? $translator->trans($errorKey, $parameters)
                    : $this->get('translator')->transChoice($errorKey, $number, $parameters),
            ]
        );
    }
}
