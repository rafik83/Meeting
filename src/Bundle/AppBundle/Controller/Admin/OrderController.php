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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\OrderListView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * @ParamConverter(
     *   "sheet",
     *   class="Proximum\Vimeet\Domain\Model\Sheet",
     *   options={"id" = "sheet_id"}
     * )
     *
     * @param Request $request
     * @param Event   $event
     * @param Sheet   $sheet
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event, Sheet $sheet)
    {
        $orders = $this
            ->get('vimeet_infrastructure.repository.order_repository')
            ->paginate($request->query->getInt('page', 1), 20, $sheet)
        ;

        $orders->setItems(
            array_map(
                function (Order $order) use ($request) {
                    return new OrderListView(
                        $order->getId(),
                        $order->getId(),
                        $order->getCreatedAt(),
                        $this->getOrderAmount($order, $request->getLocale()),
                        $order->getState(),
                        $order->getPaymentMode()
                    );
                }, $orders->getItems()
            )
        );

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetInfo($sheet)
        ;

        return $this->render(
            'VimeetAppBundle:Admin/Order:list.html.twig', [
            'event'      => $event,
            'sheet'      => $sheet,
            'sheet_info' => $sheetInfo,
            'orders'     => $orders,
        ]
        );
    }

    /**
     * @param Request $request
     * @param Order   $order
     *
     * @return Response
     */
    public function editAction(Request $request, Order $order)
    {
        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetInfo($order->getSheet())
        ;

        $orderView = $this->get('components.sheet.order_view_factory')->createFromOrder($order, $request->getLocale());

        return $this->render(
            'VimeetAppBundle:Admin/Order:edit.html.twig', [
            'sheet_info' => $sheetInfo,
            'order'      => $order,
            'order_view' => $orderView,
        ]
        );
    }

    /**
     * @param Request $request
     * @param Order   $order
     * @param string  $group
     *
     * @return RedirectResponse|Response
     */
    public function addRowAction(Request $request, Order $order, $group)
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

            return $this->redirectToRoute('admin_sheet_order_edit', ['id' => $order->getId()]);
        }

        return $this->render(
            'VimeetAppBundle:Admin/Order:addRow.html.twig', [
            'sheet_info' => $sheetInfo,
            'order'      => $order,
            'form'       => $form->createView(),
        ]
        );
    }

    /**
     * @param Request $request
     * @param Order   $order
     * @param string  $group
     * @param string  $row
     *
     * @return RedirectResponse|Response
     */
    public function updateRowAction(Request $request, Order $order, $group, $row)
    {
        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetInfo($order->getSheet())
        ;

        $updateRow = new UpdateRow($order, $group, $row);
        $form   = $this->createForm(UpdateRowType::class, $updateRow);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.order.update_row_handler')->handle($updateRow);
            $this->addFlash('success', 'flash.admin.order.update_row.success');

            return $this->redirectToRoute('admin_sheet_order_edit', ['id' => $order->getId()]);
        }

        return $this->render(
            'VimeetAppBundle:Admin/Order:updateRow.html.twig', [
            'sheet_info' => $sheetInfo,
            'order'      => $order,
            'form'       => $form->createView(),
        ]
        );
    }

    /**
     * @param Order  $order
     * @param string $group
     * @param string $row
     *
     * @return RedirectResponse
     */
    public function removeRowAction(Order $order, $group, $row)
    {
        $this->get('command.order.remove_row_handler')->handle(new RemoveRow($order, $group, $row));
        $this->addFlash('succes', 'flash.admin.order.remove_row.success');

        return $this->redirectToRoute('admin_sheet_order_edit', ['id' => $order->getId()]);
    }

    /**
     * @param Order  $order
     * @param string $locale
     *
     * @return int
     */
    private function getOrderAmount(Order $order, $locale)
    {
        $template = $this
            ->get('vimeet_infrastructure.application.components.product.product_builder')
            ->createFromOrder($order);

        $cart = $this
            ->get('vimeet_infrastructure.application.components.cart.cart_builder')
            ->generate($template, $order->getPackageData(), $locale);

        return $cart->getTotal();
    }
}
