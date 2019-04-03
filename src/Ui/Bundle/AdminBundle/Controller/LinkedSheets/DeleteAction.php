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
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\Delete;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Sheet\LinkedSheets\RemovableLinkedSheetsFilter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DeleteAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RemovableLinkedSheetsFilter */
    private $removableLinkedSheetsFilter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        RemovableLinkedSheetsFilter $removableLinkedSheetsFilter
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
        $this->removableLinkedSheetsFilter = $removableLinkedSheetsFilter;
    }

    public function __invoke(Event $event, LinkedSheets $linkedSheets)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied!');
        }

        if (count($this->removableLinkedSheetsFilter->isSatisfiedBy([$linkedSheets])) === 0) {
            throw new AccessDeniedException();
        }

        $this->commandBus->handle(new Delete($linkedSheets));
        $this->flashBag->add('success', 'flash.admin.linked_sheets.delete.success');

        return new RedirectResponse(
            $this->router->generate('admin_linked_sheets_list', ['event' => $event->getId()])
        );
    }
}
