<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Happening as CommandHappening;
use Proximum\Vimeet\Application\Exception\Happening\NotEnoughtRemainingParticipationsException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantRequiredException;
use Proximum\Vimeet\Application\Exception\Happening\WrongInvitationCodeException;
use Proximum\Vimeet\Application\Query\Happening\Participant\ParticipantsAllowedToAccessQuery;
use Proximum\Vimeet\Application\Query\Happening\Participant\ParticipantsAllowedToAccessQueryHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Happening\ParticipateType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

class ParticipateHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var CommandHappening\ParticipateHandler */
    private $participateHandler;

    /** @var ParticipantsAllowedToAccessQueryHandler */
    private $participantsAllowedToAccessQueryHandler;

    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        QuestionRepositoryInterface $questionRepository,
        CommandHappening\ParticipateHandler $participateHandler,
        ParticipantsAllowedToAccessQueryHandler $participantsAllowedToAccessQueryHandler,
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->participantRepository = $participantRepository;
        $this->questionRepository = $questionRepository;
        $this->participateHandler = $participateHandler;
        $this->participantsAllowedToAccessQueryHandler = $participantsAllowedToAccessQueryHandler;
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->translator = $translator;
    }

    public function handle(Request $request, Happening $happening, Sheet $sheet, User $user): JsonResponse
    {
        $selectedParticipants = $this->participantRepository->getParticipantsForHappening($sheet, $happening);

        $previousQuestion = null;
        $isUpdate = \count($selectedParticipants) > 0;
        $isQuestionAllowed = $happening->isQuestionAllowed();

        if ($isUpdate && $happening->isQuestionAllowed()) {
            $question = $this
                ->questionRepository
                ->getByUserAndHappening($user, $happening)
            ;

            if (null !== $question) {
                $previousQuestion = $question->getContent();
            }
        }

        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($user, $sheet);

        $isCancelParticipationAlone = $isUserAloneParticipant && $isUpdate;
        $isParticipationAlone = $isUserAloneParticipant && !$isUpdate;

        $participants = $sheet->getParticipantsArray();
        $participants = $this
            ->participantsAllowedToAccessQueryHandler
            ->handle(new ParticipantsAllowedToAccessQuery($happening, $participants))
        ;

        $availableParticipants = $this->participantRepository->getAvailableParticipantsForHappening(
            $participants,
            $happening
        );

        $noAvailableParticipants = 0 === \count($availableParticipants);

        // Case : current user is not available for this happening, do not show modal
        if ($isParticipationAlone && $noAvailableParticipants) {
            return $this->createJsonResponseWithError('happening.participate.youAreNotAvailable');
        }

        // Case : user alone in sheet and it is new participation, he is selected directly
        if ($isParticipationAlone) {
            $selectedParticipants = $participants;
        // Unselect current user
        } elseif ($isCancelParticipationAlone) {
            $selectedParticipants = [];
        }

        // Case : one participant is current user and no question so no modal
        if ($isCancelParticipationAlone || (!$happening->isPrivate() && $isParticipationAlone && !$isQuestionAllowed)) {
            try {
                $participate = new CommandHappening\Participate(
                    $happening,
                    $sheet,
                    $user,
                    $selectedParticipants,
                    $previousQuestion,
                    null,
                    $isUpdate
                );
                $this->participateHandler->handle($participate);
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

            return new JsonResponse(
                [
                    'status' => 'ok',
                    'label' => $isCancelParticipationAlone ? 'participate' : 'cancel',
                ]
            );
        }

        $participate = new CommandHappening\Participate(
            $happening,
            $sheet,
            $user,
            $selectedParticipants,
            $previousQuestion,
            null,
            $isUpdate
        );

        // Create Participate form
        $participateForm = $this->formFactory->create(
            ParticipateType::class,
            $participate,
            [
                'action' => $this->router->generate(
                    'event_sheet_happening_participate',
                    [
                        'sheet' => $sheet->getId(),
                        'happening' => $happening->getId(),
                    ]
                ),
                'method' => 'POST',
                'happening' => $happening,
                'participants' => $participants,
                'isParticipantsEnabled' => false === $isUserAloneParticipant,
                'isUpdate' => $isUpdate,
                'locale' => $request->getLocale(),
            ]
        );

        if ($participateForm->handleRequest($request)->isSubmitted() && $participateForm->isValid()) {
            $formOrParticipantsField = true === $participateForm->has('participants')
                ? $participateForm->get('participants')
                : $participateForm;

            try {
                $this->participateHandler->handle($participate);

                $label = 'participate';

                if (0 < \count($participate->participants)) {
                    $label = true === $isUserAloneParticipant ? 'cancel' : 'update';
                }

                return new JsonResponse(
                    [
                        'status' => 'ok',
                        'label' => $label,
                    ]
                );
            } catch (ParticipantNotAvailableException $participantNotAvailableException) {
                $formOrParticipantsField->addError(
                    new FormError(
                        $this->translator->trans(
                            true === $isUserAloneParticipant
                                ? 'happening.participate.youAreNotAvailable'
                                : 'happening.participate.participantNotAvailable'
                        )
                    )
                );
            } catch (ParticipantRequiredException $participantRequiredException) {
                $formOrParticipantsField->addError(
                    new FormError(
                        $this->translator->trans(
                            'happening.participate.noParticipantSelected'
                        )
                    )
                );
            } catch (NotEnoughtRemainingParticipationsException $notEnoughtRemainingParticipationsException) {
                $remainingParticipations = $notEnoughtRemainingParticipationsException->getRemainingParticipations();
                $formOrParticipantsField->addError(
                    new FormError(
                        $this->translator->transChoice(
                            'happening.participate.notEnoughtRemainingParticipations',
                            $remainingParticipations,
                            ['%remaining%' => $remainingParticipations]
                        )
                    )
                );
            } catch (WrongInvitationCodeException $wrongInvitationCodeException) {
                $formOrParticipantsField->addError(
                    new FormError(
                        $this->translator->trans(
                            'happening.participate.wrongInvitationCode'
                        )
                    )
                );
            }
        }

        $unavailableParticipants = [];

        foreach ($participants as $key => $participant) {
            if (false === \in_array($participant, $availableParticipants, true)) {
                $unavailableParticipants[$key] = $participant;
            }
        }

        return new JsonResponse(
            [
                'status' => 'show-form',
                'html' => $this->engine->render(
                    'EventBundle:Program/Partials:participate-modal.html.twig',
                    [
                        'title' => $happening->getTitle($request->getLocale()),
                        'picto' => $happening->getCategory()->getPicto(),
                        'form' => $participateForm->createView(),
                        'unavailableParticipants' => $unavailableParticipants,
                        'noAvailableParticipants' => $noAvailableParticipants,
                        'isUpdate' => $isUpdate,
                    ]
                ),
            ]
        );
    }

    private function createJsonResponseWithError(
        string $errorKey,
        array $parameters = [],
        ?int $number = null
    ): JsonResponse {
        $translator = $this->translator;

        return new JsonResponse(
            [
                'status' => 'error',
                'message' => null === $number
                    ? $translator->trans($errorKey, $parameters)
                    : $this->translator->transChoice($errorKey, $number, $parameters),
            ]
        );
    }
}
