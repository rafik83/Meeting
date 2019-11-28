<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\RegistrationPath;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\UpdateQuestion;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath\UpdateQuestionType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateQuestionAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->router = $router;
    }

    /**
     * @param Request     $request
     * @param Event       $event
     * @param Answer|null $answer
     *
     * @return Response
     */
    public function __invoke(Request $request, Event $event, Question $question): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $question->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $updateQuestion = new UpdateQuestion($question);
        $updateQuestionForm = $this->formFactory->create(
            UpdateQuestionType::class,
            $updateQuestion,
            ['event' => $event, 'submit' => true]
        );

        $updateQuestionForm->handleRequest($request);

        if ($updateQuestionForm->isSubmitted() && $updateQuestionForm->isValid()) {
            $this->commandBus->handle($updateQuestion);

            return new RedirectResponse(
                $this->router->generate('admin_type_registration_path_show', ['event' => $event->getId()])
            );
        }

        return $this->engine->renderResponse(
            '@Admin/Type/RegistrationPath/updateQuestion.html.twig',
            [
                'event' => $event,
                'form' => $updateQuestionForm->createView(),
            ]
        );
    }
}
