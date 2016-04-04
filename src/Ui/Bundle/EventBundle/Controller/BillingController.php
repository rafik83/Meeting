<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use DateTime;
use Proximum\Vimeet\Application\Command\Billing\Update;
use Proximum\Vimeet\Application\Command\Order\Create;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Billing\BillingUpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\ChoosePaymentModeType;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BillingController extends Controller
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

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createAccessDeniedException('You can not update this data');
        }

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
                    'sheet' => $sheet->getId(),
                ]);
            } catch (RequiredDataEmptyException $exception) {
                foreach ($exception->getKeys() as $key) {
                    $form->get($key)->addError(new FormError('validators.field.required'));
                }
            }
        }

        return $this->render('EventBundle:Billing:billing.html.twig', [
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

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createAccessDeniedException('You can not update this data');
        }

        $cart = $this->get('vimeet_infrastructure.repository.cart_repository')->findBySheet($sheet);

        if ($cart === null || $cart->getTemplate() !== $sheet->getTypePackageTemplate()) {
            throw $this->createNotFoundException('No cart available to complete payment');
        }

        $createOrder = new Create(
            $cart,
            $sheet,
            $cart->getData(),
            $cart->getTemplate(),
            $sheet->getBillingData(),
            $sheet->getType()->getEvent()->getBillingTemplate(),
            new DateTime()
        );
        $form = $this->createForm(ChoosePaymentModeType::class, $createOrder);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.order.create_handler')
                ->handle($createOrder);

            $this->addFlash('success', 'flash.package.payment_mode.success');

            // Go to the list of orders
            return $this->redirectToRoute('event_sheet_list_orders', [
                'sheet' => $sheet->getId(),
            ]);
        }

        $cartView = $this->get('components.sheet.cart_view_factory')->createFromCart($cart, $request->getLocale());

        return $this->render('EventBundle:Billing:paymentMode.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
            'sheet'     => $sheet,
            'cart_view' => $cartView,
        ]);
    }
}
