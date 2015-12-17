<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use DateTime;
use Proximum\Vimeet\Application\Command\Billing\CreateOrder;
use Proximum\Vimeet\Application\Command\Billing\Update;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Billing\BillingUpdateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package\ChoosePaymentModeType;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BillingController extends BaseController
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function billingAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        $update = new Update($sheet, $sheet->getBillingData());

        $form = $this->createForm(BillingUpdateType::class, $update, [
            'template' => $sheet->getEvent()->getBillingTemplate(),
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('vimeet_infrastructure.vimeet.application.command.billing.update_handler')
                    ->handle($update);

                $this->addFlash('success', 'flash.sheet.update_billing.success');

                return $this->redirectToRoute('event_sheet_package_payment_mode', [
                    'subdomain' => $request->attributes->get('subdomain'),
                    'id'        => $sheet->getId(),
                ]);
            } catch (RequiredDataEmptyException $exception) {
                $form = $this->addRequiredErrorOnForm(
                    $form,
                    $sheet->getEvent()->getBillingTemplate(),
                    $update->billingData,
                    'billingData'
                );
            }
        }

        return $this->render('VimeetAppBundle:Event/Billing:billing.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return RedirectResponse|Response
     */
    public function paymentModeAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());
        $cart = $this->get('vimeet_infrastructure.repository.cart_repository')->findBySheet($sheet);

        if ($cart === null || $cart->getTemplate() !== $sheet->getTypePackageTemplate()) {
            throw $this->createNotFoundException('No cart available to complete payment');
        }

        $createOrder = new CreateOrder(
            $cart,
            $sheet,
            Order::STATE_UNPAID,
            $sheet->getType()->getProFormaTemplate(),
            $cart->getData(),
            $cart->getTemplate(),
            $sheet->getBillingData(),
            $sheet->getType()->getEvent()->getBillingTemplate(),
            new DateTime()
        );
        $form = $this->createForm(ChoosePaymentModeType::class, $createOrder);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.billing.create_order_handler')
                ->handle($createOrder);

            $this->addFlash('success', 'flash.package.payment_mode.success');

            // Go to the list of orders
            return $this->redirectToRoute('event_sheet_list_orders', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Event/Billing:paymentMode.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
            'sheet'     => $sheet,
        ]);
    }
}
