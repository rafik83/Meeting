<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Happening\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateAction
{
    const TEMPLATE = 'AdminBundle:Happening:update.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param FormFactoryInterface                 $formFactory
     * @param EngineInterface                      $engine
     * @param RouterInterface                      $router
     * @param CommandBusInterface                  $commandBus
     * @param FlashBagInterface                    $flashBag
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        RouterInterface $router,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->router = $router;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
    }

    /**
     * @param Request     $request
     * @param Event       $event
     * @param Happening   $happening
     * @param AdminDomain $adminDomain
     *
     * @return Response
     */
    public function __invoke(Request $request, Event $event, Happening $happening, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $happening->getEvent()
        ) {
            throw new AccessDeniedException('Access Denied!');
        }

        $update = new Update($happening);
        $form   = $this->formFactory->create(UpdateType::class, $update, [
            'admin' => $adminDomain->getAdmin(),
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->flashBag->add('success', 'flash.admin.happening.update.success');

            return new RedirectResponse($this->router->generate('admin_happening_update', [
                'event'     => $event->getId(),
                'happening' => $happening->getId(),
            ]));
        }

        return $this->engine->renderResponse(self::TEMPLATE, [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
