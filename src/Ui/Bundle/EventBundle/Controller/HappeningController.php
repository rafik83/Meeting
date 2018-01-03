<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Application\Exception\Happening\NotEnoughtRemainingParticipationsException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantRequiredException;
use Proximum\Vimeet\Application\Query\Happening\Participant\ParticipantsAllowedToAccessQuery;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Happening\ParticipateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Happening\ParticipationVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
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
     * @param UserDomain  $userDomain
     *
     * @return JsonResponse
     */
    public function participateAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Happening $happening,
        UserDomain $userDomain
    ): JsonResponse {
        $event = $eventDomain->getEvent();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_HAPPENING_ACCESS', $event);
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(ParticipationVoter::PARTICIPATE, $sheet);

        if ($happening->getEvent() !== $event || $sheet->getEvent() !== $event) {
            throw $this->createNotFoundException('Happening or sheet not in this event');
        }

        $selectedParticipants = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantsForHappening($sheet, $happening);

        $previousQuestion           = null;
        $isCancelParticipationAlone = false;
        $isParticipationAlone       = false;
        $isUpdate                   = count($selectedParticipants) > 0;
        $isQuestionAllowed          = $happening->isQuestionAllowed();

        if ($isUpdate && $happening->isQuestionAllowed()) {
            $question = $this
                ->get('vimeet_infrastructure.repository.happening_question_repository')
                ->getByUserAndHappening($this->getUser(), $happening);

            if (null !== $question) {
                $previousQuestion = $question->getContent();
            }
        }

        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($userDomain->getUser(), $sheet);

        if ($isUserAloneParticipant) {
            if ($isUpdate) {
                $isCancelParticipationAlone = true;
            } else {
                $isParticipationAlone = true;
            }
        }

        $participants = $sheet->getParticipants()->toArray();
        $participants = $this
            ->get('tactician.commandbus.query')
            ->handle(new ParticipantsAllowedToAccessQuery($happening, $participants))
        ;

        $availableParticipants = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getAvailableParticipantsForHappening($participants, $happening);

        $noAvailableParticipants = 0 === count($availableParticipants);

        // Case : user alone in sheet and it is new participation, he is selected directly
        if ($isParticipationAlone) {
            $selectedParticipants = $participants;

            // Case : current user is not available for this happening, do not show modal
            if ($noAvailableParticipants) {
                return $this->createJsonResponseWithError('happening.participate.youAreNotAvailable');
            }
        } elseif ($isCancelParticipationAlone) {
            // Unselect current user
            $selectedParticipants = [];
        }

        // Case : one participant is current user and no question so no modal
        if ($isParticipationAlone && !$isQuestionAllowed || $isCancelParticipationAlone) {
            try {
                $participate = new Participate($happening, $sheet, $userDomain->getUser(), $selectedParticipants);
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

            return new JsonResponse([
                'status' => 'ok',
                'label'  => $isCancelParticipationAlone ? 'participate' : 'cancel',
            ]);
        }

        $participate = new Participate(
            $happening,
            $sheet,
            $userDomain->getUser(),
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

                $label = 'participate';

                if (0 < count($participate->participants)) {
                    $label = true === $isUserAloneParticipant ? 'cancel' : 'update';
                }

                return new JsonResponse([
                    'status' => 'ok',
                    'label'  => $label,
                ]);
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
                    'noAvailableParticipants' => $noAvailableParticipants,
                    'isUpdate'                => $isUpdate,
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
