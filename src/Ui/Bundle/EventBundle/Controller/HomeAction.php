<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\RegistrationPath\AnswerView;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathQuery;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathView;
use Proximum\Vimeet\Application\Query\RegistrationPath\QuestionView;
use Proximum\Vimeet\Application\Query\RegistrationPath\TypeView as RegistrationPathTypeView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route\HomeUserDispatcher;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\RegistrationPath\QuestionType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Type\TypeChoiceType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class HomeAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var Environment */
    private $twig;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var HomeUserDispatcher */
    private $homeUserDispatcher;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        Environment $twig,
        FormFactoryInterface $formFactory,
        HomeUserDispatcher $homeUserDispatcher,
        QueryBusInterface $queryBus,
        RouterInterface $router,
        TranslatorInterface $translator,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->homeUserDispatcher = $homeUserDispatcher;
        $this->queryBus = $queryBus;
        $this->router = $router;
        $this->translator = $translator;
        $this->typeRepository = $typeRepository;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        ?UserInterface $user = null,
        ?int $question = null,
        ?int $answer = null
    ): Response {
        $locale = $request->getLocale();
        $event = $eventDomain->getEvent();

        $response = $this->homeUserDispatcher->attemptDispatchUser($event, $user);

        if ($response instanceof RedirectResponse) {
            return $response;
        }

        /** @var EventRegistrationPathView $eventRegistrationPathView */
        $eventRegistrationPathView = $this->queryBus->handle(new EventRegistrationPathQuery($event, $locale));

        if ($eventRegistrationPathView->hasRegistrationPath()) {
            return $this->followRegistrationPath($request, $event, $eventRegistrationPathView, $question, $answer);
        }

        $result = $this->getTypeChoiceFormViewOrRedirect($request, $event, $locale);

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        return new Response(
            $this->twig->render(
                'EventBundle:Home:index.html.twig',
                [
                    'event' => $event,
                    'form' => $result,
                    'questionView' => null,
                    'backLink' => null,
                ]
            )
        );
    }

    private function followRegistrationPath(
        Request $request,
        Event $event,
        EventRegistrationPathView $eventRegistrationPathView,
        ?int $questionId,
        ?int $answerId
    ): Response {
        if (null !== $answerId) {
            return $this->manageAnswer($request, $event, $eventRegistrationPathView, $questionId, $answerId);
        }

        return $this->manageQuestion($request, $event, $eventRegistrationPathView, $questionId);
    }

    /**
     * @param Request    $request
     * @param Event      $event
     * @param string     $locale
     * @param TypeView[] $filteredTypeViews
     *
     * @return FormView|RedirectResponse|null
     */
    private function getTypeChoiceFormViewOrRedirect(
        Request $request,
        Event $event,
        string $locale,
        array $filteredTypeViews = []
    ) {
        $typeViews = $this->getFilteredTypeViews($event, $locale, $filteredTypeViews);

        if (empty($typeViews) && !$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return new RedirectResponse($this->router->generate('event_login'));
        }

        if (empty($typeViews)) {
            return null;
        }

        $form = $this->formFactory->create(
            TypeChoiceType::class,
            null,
            [
                'typeViews' => $typeViews,
            ]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $typeView = $form->getData()['type'];

            if (null === $typeView) {
                $form->get('type')->addError(
                    new FormError($this->translator->trans('validators.type.required', [], 'validators'))
                );
            } else {
                return $this->redirectToTypeRegistration($typeView->id);
            }
        }

        return $form->createView();
    }

    /**
     * @param Event                      $event
     * @param string                     $locale
     * @param RegistrationPathTypeView[] $filteredTypeViews
     *
     * @return TypeView[]
     */
    private function getFilteredTypeViews(Event $event, string $locale, array $filteredTypeViews = []): array
    {
        $typeViews = $this->typeRepository->getVisibleTypesViewsByEvent($event, $locale);

        if (empty($filteredTypeViews)) {
            return $typeViews;
        }

        foreach ($typeViews as $key => $typeView) {
            if (!$this->hasType($typeView, $filteredTypeViews)) {
                unset($typeViews[$key]);
            }
        }

        return array_values($typeViews);
    }

    /**
     * @param TypeView   $typeView
     * @param TypeView[] $filteredTypeViews
     *
     * @return bool
     */
    private function hasType($typeView, array &$filteredTypeViews)
    {
        foreach ($filteredTypeViews as $filteredTypeView) {
            if ($filteredTypeView->id === $typeView->id) {
                return true;
            }
        }

        return false;
    }

    private function redirectToTypeRegistration(int $typeId): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return new RedirectResponse(
                $this->router->generate('event_participate', ['typeView' => $typeId])
            );
        }

        return new RedirectResponse(
            $this->router->generate('event_register', ['typeView' => $typeId])
        );
    }

    private function manageQuestion(
        Request $request,
        Event $event,
        EventRegistrationPathView $eventRegistrationPathView,
        ?int $questionId
    ): Response {
        if (null === $questionId) {
            return new RedirectResponse(
                $this->router->generate(
                    'event_registration_path_question',
                    ['question' => $eventRegistrationPathView->questionView->id]
                )
            );
        }

        $questionView = $eventRegistrationPathView->getQuestionViewById($questionId);

        if (!$questionView instanceof QuestionView) {
            throw new NotFoundHttpException('Question not found');
        }

        $questionForm = $this->formFactory->create(
            QuestionType::class,
            null,
            [
                'questionView' => $questionView,
            ]
        );

        if ($questionForm->handleRequest($request)->isSubmitted() && $questionForm->isValid()) {
            $answerView = $questionForm->getData()['answer'];

            if ($answerView instanceof AnswerView) {
                if ($answerView->hasTypes()) {
                    if ($answerView->hasOneType()) {
                        return $this->redirectToTypeRegistration($answerView->getTypeView()->id);
                    }

                    return new RedirectResponse(
                        $this->router->generate(
                            'event_registration_path_answer',
                            ['question' => $questionView->id, 'answer' => $answerView->id]
                        )
                    );
                }

                if (null !== $answerView->nextQuestionView) {
                    return new RedirectResponse(
                        $this->router->generate(
                            'event_registration_path_question',
                            ['question' => $answerView->nextQuestionView->id]
                        )
                    );
                }
            }
        }

        return new Response(
            $this->twig->render(
                'EventBundle:Home:index.html.twig',
                [
                    'event' => $event,
                    'form' => null,
                    'questionView' => $questionView,
                    'questionForm' => $questionForm->createView(),
                    'backLink' => $this->getQuestionBackLink($questionView),
                ]
            )
        );
    }

    private function manageAnswer(
        Request $request,
        Event $event,
        EventRegistrationPathView $eventRegistrationPathView,
        int $questionId,
        int $answerId
    ): Response {
        $answerView = $eventRegistrationPathView->getAnswerViewById($answerId);

        if (!$answerView instanceof AnswerView) {
            throw new NotFoundHttpException('Answer not found');
        }

        if ($questionId !== $answerView->questionView->id) {
            throw new NotFoundHttpException('Question not found');
        }

        $result = $this->getTypeChoiceFormViewOrRedirect(
            $request,
            $event,
            $request->getLocale(),
            $answerView->typeViews
        );

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        return new Response(
            $this->twig->render(
                'EventBundle:Home:index.html.twig',
                [
                    'event' => $event,
                    'form' => $result,
                    'questionView' => null,
                    'backLink' => $this->getAnswerBackLink($answerView),
                ]
            )
        );
    }

    private function getQuestionBackLink(QuestionView $questionView): ?string
    {
        if (null === $questionView->previousAnswerView) {
            return null;
        }

        return $this->router->generate(
            'event_registration_path_question',
            ['question' => $questionView->previousAnswerView->questionView->id]
        );
    }

    private function getAnswerBackLink(AnswerView $answerView): ?string
    {
        return $this->router->generate(
            'event_registration_path_question',
            ['question' => $answerView->questionView->id]
        );
    }
}
