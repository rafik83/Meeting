<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\Badge;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\Badge\Configure;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Badge\ConfigureType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConfigureAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    /** @var BadgeRepositoryInterface */
    private $badgeRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        BadgeRepositoryInterface $badgeRepository,
        CommandBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        EngineInterface $engine,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->badgeRepository = $badgeRepository;
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
        $this->engine = $engine;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event, Type $type): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $type->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $badge = $this->badgeRepository->findByType($type);

        $configure = new Configure($event, $type, $badge);
        $form = $this->formFactory->create(ConfigureType::class, $configure, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($configure);
            $this->flashBag->add('success', 'flash.admin.type.badge.configuration.success');

            return new RedirectResponse($this->router->generate('admin_type_badge_configuration', [
                'event' => $event->getId(),
                'type' => $type->getId(),
            ]));
        }

        return $this->engine->renderResponse('AdminBundle:Type/Badge:configure.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'badge' => $badge,
            'type' => $type,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);
    }
}
