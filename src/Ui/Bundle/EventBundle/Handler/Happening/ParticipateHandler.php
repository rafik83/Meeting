<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Happening as CommandHappening;
use Proximum\Vimeet\Application\Exception\Happening\MaxNumberHappeningParticipationReachedException;
use Proximum\Vimeet\Application\Exception\Happening\NotEnoughtRemainingParticipationsException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantMustHaveProductToParticipateException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantRequiredException;
use Proximum\Vimeet\Application\Exception\Happening\WrongInvitationCodeException;
use Proximum\Vimeet\Application\Query\Happening\Participant\ParticipantsAllowedToAccessQuery;
use Proximum\Vimeet\Application\Query\Happening\Participant\ParticipantsAllowedToAccessQueryHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Domain\Happening\PackageProductsNeededByHappening;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningWithProductToBuyChecker;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
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

    /** @var ParticipateToHappeningWithProductToBuyChecker */
    private $participateToHappeningWithProductToBuyChecker;

    /** @var PackageProductsNeededByHappening */
    private $packageProductsNeededByHappening;

    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;
    /** @var CanAccessToWebinar */
    private $canAccessToWebinar;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        QuestionRepositoryInterface $questionRepository,
        CommandHappening\ParticipateHandler $participateHandler,
        ParticipantsAllowedToAccessQueryHandler $participantsAllowedToAccessQueryHandler,
        ParticipateToHappeningWithProductToBuyChecker $participateToHappeningWithProductToBuyChecker,
        PackageProductsNeededByHappening $packageProductsNeededByHappening,
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        TranslatorInterface $translator,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        CanAccessToWebinar $canAccessToWebinar
    ) {
        $this->participantRepository = $participantRepository;
        $this->questionRepository = $questionRepository;
        $this->participateHandler = $participateHandler;
        $this->participantsAllowedToAccessQueryHandler = $participantsAllowedToAccessQueryHandler;
        $this->participateToHappeningWithProductToBuyChecker = $participateToHappeningWithProductToBuyChecker;
        $this->packageProductsNeededByHappening = $packageProductsNeededByHappening;
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->translator = $translator;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->canAccessToWebinar = $canAccessToWebinar;
    }

    public function handle(Request $request, Happening $happening, Sheet $sheet, User $user): JsonResponse
    {
        $numberMaxOfHappeningsPerUser = $sheet->getType()->getNumberMaxOfHappeningsPerUser();
        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($user, $sheet);
        $participant = $sheet->getUserParticipant($user);
        $selectedParticipants = $this->participantRepository->getParticipantsForHappening($sheet, $happening);
        $isUpdate = \count($selectedParticipants) > 0;
        $isCancelParticipationAlone = $isUserAloneParticipant && $isUpdate;
        $isParticipationAlone = $isUserAloneParticipant && !$isUpdate;
        $numberUserHappeningParticipation = $numberMaxOfHappeningsPerUser === null && $isParticipationAlone ? 0 : $this->happeningParticipationRepository->countByUserAndEvent($user, $sheet->getEvent());

        if ($numberMaxOfHappeningsPerUser && $isParticipationAlone && $numberUserHappeningParticipation >= $numberMaxOfHappeningsPerUser) {
            return new JsonResponse(
                [
                    'status' => 'show-form',
                    'html' => $this->engine->render(
                        'EventBundle:Program/Partials:max-number-happening-participation-reached.html.twig',
                        [
                            'title' => $happening->getTitle($request->getLocale())
                        ]
                    )
                ]
            );
        }

        $participants = $this
            ->participantsAllowedToAccessQueryHandler
            ->handle(new ParticipantsAllowedToAccessQuery($happening, $sheet->getParticipantsArray()))
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

        // Case : one participant is current user and no question so no modal
        if ($participant instanceof Participant
            && ($isCancelParticipationAlone
                || ($isParticipationAlone
                    && !$happening->isPrivate()
                    && !$happening->isQuestionAllowed()
                    && $this->participateToHappeningWithProductToBuyChecker->canParticipate($participant, $happening)
                )
            )
        ) {
            return $this->handleParticipationWithoutShowingForm($happening, $participant, $isCancelParticipationAlone);
        }

        return $this->handleParticipationWithShowingForm(
            $request,
            $happening,
            $sheet,
            $user,
            $participants,
            $availableParticipants,
            $selectedParticipants,
            $numberMaxOfHappeningsPerUser
        );
    }

    private function handleParticipationWithoutShowingForm(
        Happening $happening,
        Participant $participant,
        bool $isCancel
    ): JsonResponse {
        try {
            $participate = new CommandHappening\Participate(
                $happening,
                $participant->getSheet(),
                $participant->getUser(),
                $isCancel ? [] : [$participant],
                null,
                null,
                $isCancel
            );
            $this->participateHandler->handle($participate);
        } catch (ParticipantNotAvailableException $participantNotAvailableException) {
            return $this->createJsonResponseWithError('happening.participate.youAreNotAvailable');
        } catch (ParticipantMustHaveProductToParticipateException $participantMustHaveProductToParticipateException) {
            return $this->createJsonResponseWithError('happening.participate.participantMustHaveProductToParticipate');
        } catch (NotEnoughtRemainingParticipationsException $notEnoughtRemainingParticipationsException) {
            $remainingParticipations = $notEnoughtRemainingParticipationsException->getRemainingParticipations();

            return $this->createJsonResponseWithError(
                'happening.participate.notEnoughtRemainingParticipations',
                ['%remaining%' => $remainingParticipations],
                $remainingParticipations
            );
        }

        if (!$isCancel && $this->canAccessToWebinar->isSatisfiableBy($happening, $participant->getUser())) {
            return new JsonResponse(
                [
                    'status' => 'ok',
                    'label' => 'redirect',
                    'redirectTo' => $this->router->generate(
                        'event_sheet_happening_webinar',
                        ['happening' => $happening->getId(), 'sheet' => $participant->getSheet()->getId()]
                    ),
                ]
            );
        }

        return new JsonResponse(
            [
                'status' => 'ok',
                'label' => $isCancel ? 'participate' : 'cancel',
            ]
        );
    }

    /**
     * @param Request $request
     * @param Happening $happening
     * @param Sheet $sheet
     * @param User $user
     * @param Participant[] $participants
     * @param Participant[] $availableParticipants
     * @param Participant[] $selectedParticipants
     * @param int|null $numberMaxOfHappeningsPerUser
     *
     * @return JsonResponse
     */
    private function handleParticipationWithShowingForm(
        Request $request,
        Happening $happening,
        Sheet $sheet,
        User $user,
        array &$participants,
        array &$availableParticipants,
        array &$selectedParticipants,
        $numberMaxOfHappeningsPerUser
    ): JsonResponse {
        $isUpdate = \count($selectedParticipants) > 0;
        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($user, $sheet);

        $participate = new CommandHappening\Participate(
            $happening,
            $sheet,
            $user,
            empty($selectedParticipants) && $isUserAloneParticipant ? $participants : $selectedParticipants,
            $this->getPreviousQuestionContent($happening, $user),
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

                if ($this->canAccessToWebinar->isSatisfiableBy($happening, $user)) {
                    return new JsonResponse(
                        [
                            'status' => 'ok',
                            'label' => 'redirect',
                            'redirectTo' => $this->router->generate(
                                'event_sheet_happening_webinar',
                                ['happening' => $happening->getId(), 'sheet' => $sheet->getId()]
                            ),
                        ]
                    );
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
                $participateForm->get('invitationCode')->addError(
                    new FormError(
                        $this->translator->trans(
                            'happening.participate.wrongInvitationCode'
                        )
                    )
                );
            } catch (ParticipantMustHaveProductToParticipateException $participantMustHaveProductToParticipateException) {
                $formOrParticipantsField->addError(
                    new FormError(
                        $this->translator->trans(
                            'happening.participate.participantMustHaveProductToParticipate'
                        )
                    )
                );
            } catch (MaxNumberHappeningParticipationReachedException $maxNumberHappeningParticipationReachedException) {
                $formOrParticipantsField->addError(
                    new FormError(
                        $this->translator->trans(
                            'happening.participate.maxNumberHappeningParticipationReachedForParticipant',
                            [
                                '%fullname%' => $maxNumberHappeningParticipationReachedException->getParticipant()->getFullname()
                            ]
                        )
                    )
                );
            }
        }

        $participantsCanNotParticipate = $this->getParticipantsCanNotParticipate(
            $happening,
            $participants
        );

        $productsNeededByHappening = empty($participantsCanNotParticipate)
            ? []
            : $this->packageProductsNeededByHappening->get(
                $sheet->getPackage(),
                $happening
            );

        $noParticipantCanParticipate = \count($participantsCanNotParticipate) === \count($participants);

        return new JsonResponse(
            [
                'status' => 'show-form',
                'html' => $this->engine->render(
                    'EventBundle:Program/Partials:participate-modal.html.twig',
                    [
                        'event' => $sheet->getEvent(),
                        'sheet' => $sheet,
                        'title' => $happening->getTitle($request->getLocale()),
                        'picto' => $happening->getCategory()->getPicto(),
                        'form' => $participateForm->createView(),
                        'unavailableParticipants' => $this->getUnavailableParticipants(
                            $participants,
                            $availableParticipants
                        ),
                        'noAvailableParticipants' => 0 === count($availableParticipants),
                        'isUpdate' => $isUpdate,
                        'participantsCanNotParticipate' => $participantsCanNotParticipate,
                        'productsNeededByHappening' => $productsNeededByHappening,
                        'noParticipantCanParticipate' => $noParticipantCanParticipate,
                        'locale' => $request->getLocale(),
                        'maxNumberHappeningParticipationReached' => $this->getMaxNumberHappeningParticipationReached($participants, $numberMaxOfHappeningsPerUser, $sheet)
                    ]
                ),
            ]
        );
    }

    /**
     * @param Happening     $happening
     * @param Participant[] $participants
     *
     * @return Participant[]
     */
    private function getParticipantsCanNotParticipate(Happening $happening, array &$participants): array
    {
        $participantsCanNotParticipate = [];

        foreach ($participants as $key => $participant) {
            if (!$this->participateToHappeningWithProductToBuyChecker->canParticipate($participant, $happening)) {
                $participantsCanNotParticipate[$key] = $participant;
            }
        }

        return $participantsCanNotParticipate;
    }

    private function getPreviousQuestionContent(Happening $happening, User $user): ?string
    {
        if ($happening->isQuestionAllowed()) {
            $questions = $this
                ->questionRepository
                ->getByUserAndHappening($user, $happening)
            ;

            return implode("\n", array_map(static function (Happening\Question $question) {
                return $question->getContent();
            }, $questions));
        }

        return null;
    }

    /**
     * @param Participant[] $participants
     * @param Participant[] $availableParticipants
     *
     * @return Participant[]
     */
    private function getUnavailableParticipants(array &$participants, array &$availableParticipants): array
    {
        $unavailableParticipants = [];

        foreach ($participants as $key => $participant) {
            if (false === \in_array($participant, $availableParticipants, true)) {
                $unavailableParticipants[$key] = $participant;
            }
        }

        return $unavailableParticipants;
    }

    /**
     * @param Participant[] $participants
     * @param int|null $numberMaxOfHappeningsPerUser
     * @param Sheet $sheet
     *
     * @return Participant[]
     */
    private function getMaxNumberHappeningParticipationReached(array $participants, ?int $numberMaxOfHappeningsPerUser, Sheet $sheet): array
    {
        $maxNumberHappeningParticipationReached = [];

        if (null === $numberMaxOfHappeningsPerUser) {
            return [];
        }

        foreach ($participants as $key => $participant) {
            if ($this->happeningParticipationRepository->countByUserAndEvent($participant->getUser(), $sheet->getEvent()) >= $numberMaxOfHappeningsPerUser) {
                $maxNumberHappeningParticipationReached[$key] = $participant;
            }
        }

        return $maxNumberHappeningParticipationReached;
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
