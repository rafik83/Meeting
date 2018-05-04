<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event\AuthenticationToken;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\User\Event\AuthenticationTokenImport;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\Event\AuthenticationTokenType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConfirmImportAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommandBusInterface $commandBus,
        RouterInterface $router
    ) {
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event, File $file): Response
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Access denied');
        }
    }
}
