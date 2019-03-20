<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\LinkedSheets;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\Create;
use Proximum\Vimeet\Application\Query\Sheet\SheetsForNewLinkedSheetsListView;
use Proximum\Vimeet\Application\Query\Sheet\SheetsForNewLinkedSheetsQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\SheetView;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\LinkedSheets\CreateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AddAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus,
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        $sheetsForNewLinkedSheetsQuery = new SheetsForNewLinkedSheetsQuery($event);
        /** @var SheetView[] $sheetsNewLinkedSheetsView */
        $sheetsNewLinkedSheetsView = $this->queryBus->handle($sheetsForNewLinkedSheetsQuery);

        $command = new Create($event);
        $form = $this->formFactory->create(
            CreateType::class,
            $command,
            ['sheetViews' => $sheetsNewLinkedSheetsView]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->flashBag->add('success', 'flash.admin.linked_sheets.create.success');

            return new RedirectResponse(
                $this->router->generate('admin_linked_sheets_list', ['event' => $event->getId()])
            );
        }

        return $this->engine->renderResponse(
            '@Admin/LinkedSheets/create.html.twig',
            [
                'event' => $event,
                'form'  => $form->createView(),
            ]
        );
    }
}
