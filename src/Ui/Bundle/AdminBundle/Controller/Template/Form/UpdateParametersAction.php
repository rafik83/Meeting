<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Template\Form\UpdateParameters;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Form\UpdateParametersType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class UpdateParametersAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event, FormTemplate $formTemplate): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $formTemplate->getEvent()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $updateParameters = new UpdateParameters($formTemplate);
        $form = $this->formFactory->create(UpdateParametersType::class, $updateParameters, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($updateParameters);

            return new RedirectResponse($this->router->generate('admin_template_form_list', [
                'event' => $event->getId()
            ]));
        }

        return new Response($this->engine->render('AdminBundle:Template/Form:updateParameters.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]));
    }
}
