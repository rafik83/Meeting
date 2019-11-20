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
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Type\RegistrationPath\View\AddQuestion;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath\AddQuestionType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AddQuestionAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        FormFactoryInterface $formFactory,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     * @throws AccessDeniedException
     *
     */
    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $addQuestion = new AddQuestion();
        $addQuestionForm = $this->formFactory->create(
            AddQuestionType::class,
            $addQuestion,
            ['event' => $event, 'submit' => true]
        );

        $addQuestionForm->handleRequest($request);

        if ($addQuestionForm->isSubmitted() && $addQuestionForm->isValid()) {
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
