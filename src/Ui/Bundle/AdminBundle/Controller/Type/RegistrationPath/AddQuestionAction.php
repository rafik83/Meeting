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
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\AddQuestion;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath\AddQuestionType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AddQuestionAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->router = $router;
    }

    /**
     * @param Request     $request
     * @param Event       $event
     * @param Answer|null $answer
     *
     * @return Response
     */
    public function __invoke(Request $request, Event $event, ?Answer $answer): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || (null !== $answer && $answer->getEvent() !== $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        // @todo: check if answer is null, event has already a first question
        // @todo: check if answer has not already a next step

        $addQuestion = new AddQuestion($event, $answer);
        $addQuestionForm = $this->formFactory->create(
            AddQuestionType::class,
            $addQuestion,
            ['event' => $event, 'submit' => true]
        );

        $addQuestionForm->handleRequest($request);

        if ($addQuestionForm->isSubmitted() && $addQuestionForm->isValid()) {
            $this->commandBus->handle($addQuestion);

            return new RedirectResponse(
                $this->router->generate('admin_type_registration_path_show', ['event' => $event->getId()])
            );
        }

        return $this->engine->renderResponse(
            '@Admin/Type/RegistrationPath/addQuestion.html.twig',
            [
                'event' => $event,
                'form' => $addQuestionForm->createView(),
            ]
        );
    }
}
