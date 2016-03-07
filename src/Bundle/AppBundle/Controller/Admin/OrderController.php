<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Order\AddRow;
use Proximum\Vimeet\Application\Command\Order\RemoveRow;
use Proximum\Vimeet\Application\Command\Order\UpdateRow;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Order\AddRowType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Order\UpdateRowType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     * @param Order   $order
     *
     * @return Response
     */
    public function editAction(Request $request, Event $event, Order $order)
    {
        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetInfo($order->getSheet())
        ;

        $orderView = $this->get('components.sheet.order_view_factory')->createFromOrder(
            $order,
            $event->getAvailableLocale($request->getLocale())
        );

        return $this->render(
            'VimeetAppBundle:Admin/Order:edit.html.twig',
            [
                'event'      => $event,
                'sheet_info' => $sheetInfo,
                'order'      => $order,
                'order_view' => $orderView,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Order   $order
     * @param string  $group
     *
     * @return RedirectResponse|Response
     */
    public function addRowAction(Request $request, Event $event, Order $order, $group)
    {
        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetInfo($order->getSheet())
        ;

        $addRow = new AddRow($order, $group);
        $form   = $this->createForm(AddRowType::class, $addRow);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.order.add_row_handler')->handle($addRow);
            $this->addFlash('success', 'flash.admin.order.add_row.success');

            return $this->redirectToRoute('admin_sheet_order_edit', [
                'event' => $event->getId(),
                'order' => $order->getId(),
            ]);
        }

        return $this->render(
            'VimeetAppBundle:Admin/Order:addRow.html.twig',
            [
                'event'      => $event,
                'sheet_info' => $sheetInfo,
                'order'      => $order,
                'form'       => $form->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Order   $order
     * @param string  $group
     * @param string  $row
     *
     * @return RedirectResponse|Response
     */
    public function updateRowAction(Request $request, Event $event, Order $order, $group, $row)
    {
        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetInfo($order->getSheet())
        ;

        $updateRow = new UpdateRow($order, $group, $row);
        $form      = $this->createForm(UpdateRowType::class, $updateRow);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.order.update_row_handler')->handle($updateRow);
            $this->addFlash('success', 'flash.admin.order.update_row.success');

            return $this->redirectToRoute('admin_sheet_order_edit', [
                'event' => $event->getId(),
                'order' => $order->getId(),
            ]);
        }

        return $this->render(
            'VimeetAppBundle:Admin/Order:updateRow.html.twig',
            [
                'event'      => $event,
                'sheet_info' => $sheetInfo,
                'order'      => $order,
                'form'       => $form->createView(),
            ]
        );
    }

    /**
     * @param Event  $event
     * @param Order  $order
     * @param string $group
     * @param string $row
     *
     * @return RedirectResponse
     */
    public function removeRowAction(Event $event, Order $order, $group, $row)
    {
        $this->get('command.order.remove_row_handler')->handle(new RemoveRow($order, $group, $row));
        $this->addFlash('success', 'flash.admin.order.remove_row.success');

        return $this->redirectToRoute('admin_sheet_order_edit', [
            'event' => $event->getId(),
            'order' => $order->getId(),
        ]);
    }
}
