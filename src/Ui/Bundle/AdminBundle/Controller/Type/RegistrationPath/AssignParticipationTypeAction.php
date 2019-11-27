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
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\AssignParticipationType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath\AssignParticipationTypeType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AssignParticipationTypeAction
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
     * @param AdminDomain $adminDomain
     * @param Event       $event
     * @param Answer|null $answer
     *
     * @return Response
     */
    public function __invoke(Request $request, AdminDomain $adminDomain, Event $event, Answer $answer): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $answer->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        // @todo: check if answer has not already a next step

        $admin = $adminDomain->getAdmin();
        $locale = $event->getAvailableLocale($request->getLocale());

        $assignParticipationType = new AssignParticipationType($answer);
        $assignParticipationTypeForm = $this->formFactory->create(
            AssignParticipationTypeType::class,
            $assignParticipationType,
            ['event' => $event, 'locale' => $locale, 'admin' => $admin, 'submit' => true]
        );

        $assignParticipationTypeForm->handleRequest($request);

        if ($assignParticipationTypeForm->isSubmitted() && $assignParticipationTypeForm->isValid()) {
            //$this->commandBus->handle($assignParticipationTypeForm);

            return new RedirectResponse(
                $this->router->generate('admin_type_registration_path_show', ['event' => $event->getId()])
            );
        }

        return $this->engine->renderResponse(
            '@Admin/Type/RegistrationPath/assignParticipationType.html.twig',
            [
                'event' => $event,
                'questionTitle' => $answer->getQuestion()->getTitle($locale),
                'answerTitle' => $answer->getTitle($locale),
                'form' => $assignParticipationTypeForm->createView(),
            ]
        );
    }
}
