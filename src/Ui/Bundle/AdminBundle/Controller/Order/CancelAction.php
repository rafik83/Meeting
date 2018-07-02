<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Order;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Order\Cancel;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CancelAction
{
    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
    }

    public function __invoke(Event $event, Sheet $sheet, Order $order): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || $event !== $sheet->getEvent()
            || $order->getSheet() !== $sheet
            || $order->hasInvoice()
            || $order->isCancelled()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $this->commandBus->handle(new Cancel($order));
        $this->flashBag->add('success', 'flash.admin.order.cancel.success');

        return new RedirectResponse(
            $this->router->generate('admin_sheet_details', [
                'event' => $event->getId(),
                'sheet' => $sheet->getId(),
            ])
        );
    }
}
