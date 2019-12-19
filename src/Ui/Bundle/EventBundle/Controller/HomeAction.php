<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\RegistrationPath\AnswerView;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathQuery;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathView;
use Proximum\Vimeet\Application\Query\RegistrationPath\QuestionView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route\HomeUserDispatcher;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\RegistrationPath\QuestionType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Type\TypeChoiceType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Templating\EngineInterface;

class HomeAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

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
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        HomeUserDispatcher $homeUserDispatcher,
        QueryBusInterface $queryBus,
        RouterInterface $router,
        TranslatorInterface $translator,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
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
        ?int $question = null
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
            return $this->followRegistrationPath($request, $event, $eventRegistrationPathView, $question);
        }

        $typeViews = $this->typeRepository->getVisibleTypesViewsByEvent($event, $locale);

        if (empty($typeViews) && !$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return new RedirectResponse($this->router->generate('event_login'));
        }

        $formView = null;

        if (!empty($typeViews)) {
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

            $formView = $form->createView();
        }

        return new Response(
            $this->engine->render(
                'EventBundle:Home:index.html.twig',
                [
                    'event' => $event,
                    'form' => $formView,
                    'questionView' => null,
                ]
            )
        );
    }

    private function followRegistrationPath(
        Request $request,
        Event $event,
        EventRegistrationPathView $eventRegistrationPathView,
        ?int $questionId
    ): Response {
        $questionView = $eventRegistrationPathView->questionView;

        if (null !== $questionId) {
            $questionView = $eventRegistrationPathView->getQuestionViewById($questionId);

            if (!$questionView instanceof QuestionView) {
                throw new NotFoundHttpException('Question not found');
            }
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

                    // @todo: several types
                }

                if (null !== $answerView->nextQuestionView) {
                    return new RedirectResponse(
                        $this->router->generate('event_question', ['question' => $answerView->nextQuestionView->id])
                    );
                }
            }
        }

        return new Response(
            $this->engine->render(
                'EventBundle:Home:index.html.twig',
                [
                    'event' => $event,
                    'form' => null,
                    'questionView' => $questionView,
                    'questionForm' => $questionForm->createView(),
                ]
            )
        );
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
}
