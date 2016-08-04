<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Order\AddRow;
use Proximum\Vimeet\Application\Command\Order\RemoveRow;
use Proximum\Vimeet\Application\Command\Order\UpdateRow;
use Proximum\Vimeet\Application\Query\Order\PaginatedOrderListViewQuery;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\AddRowType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\FilterType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\UpdateRowType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $filters = [];
        $filterForm = $this->createFilterForm(
            FilterType::class,
            $filters,
            ['event' => $event, 'locale' => $locale]
        );
        $filtered = $filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid();

        if ($filtered) {
            $filters = $filterForm->getData();
        }

        $query = new PaginatedOrderListViewQuery(
            $event,
            $filters,
            $request->query->getInt('page', 1),
            20,
            $locale
        );
        $orders = $this->get('tactician.commandbus.query')->handle($query);

        return $this->render('AdminBundle:Order:list.html.twig', [
            'event'      => $event,
            'orders'     => $orders,
            'filterForm' => $filterForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Order   $order
     *
     * @return Response
     *
     * @deprecated need to be rewritten
     */
    public function editAction(Request $request, Event $event, Order $order)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessIfOrderNotInEvent($event, $order);

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetName($order->getSheet(), $request->getLocale())
        ;

        $orderView = null;

        return $this->render(
            'AdminBundle:Order:edit.html.twig',
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
        $this->denyAccessIfOrderNotInEvent($event, $order);

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetName($order->getSheet(), $request->getLocale())
        ;

        $addRow = new AddRow($order, $group);
        $form   = $this->createForm(AddRowType::class, $addRow);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($addRow);
            $this->addFlash('success', 'flash.admin.order.add_row.success');

            return $this->redirectToRoute('admin_sheet_order_edit', [
                'event' => $event->getId(),
                'order' => $order->getId(),
            ]);
        }

        return $this->render(
            'AdminBundle:Order:addRow.html.twig',
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
        $this->denyAccessIfOrderNotInEvent($event, $order);

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetName($order->getSheet(), $request->getLocale());

        $updateRow = new UpdateRow($order, $group, $row);
        $form      = $this->createForm(UpdateRowType::class, $updateRow);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($updateRow);
            $this->addFlash('success', 'flash.admin.order.update_row.success');

            return $this->redirectToRoute('admin_sheet_order_edit', [
                'event' => $event->getId(),
                'order' => $order->getId(),
            ]);
        }

        return $this->render(
            'AdminBundle:Order:updateRow.html.twig',
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
        $this->denyAccessIfOrderNotInEvent($event, $order);

        $this->get('tactician.commandbus')->handle(new RemoveRow($order, $group, $row));
        $this->addFlash('success', 'flash.admin.order.remove_row.success');

        return $this->redirectToRoute('admin_sheet_order_edit', [
            'event' => $event->getId(),
            'order' => $order->getId(),
        ]);
    }

    /**
     * @param Event $event
     * @param Order $order
     */
    private function denyAccessIfOrderNotInEvent(Event $event, Order $order)
    {
        if ($order->getSheet()->getEvent() !== $event) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @param string $type
     * @param array  $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, array_merge($options, [
            'method'             => 'GET',
            'csrf_protection'    => false,
            'required'           => false,
            'allow_extra_fields' => true,
        ]));
    }
}
