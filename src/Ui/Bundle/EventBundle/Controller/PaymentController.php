<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Order\Create;
use Proximum\Vimeet\Application\Command\Payment\Choice;
use Proximum\Vimeet\Application\Command\Payment\ChoiceWithDeposit;
use Proximum\Vimeet\Application\Exception\Payment\DepositNotAvailableException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Payment\DepositApplicable;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment\PaymentChoiceType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment\PaymentChoiceWithDepositType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function paymentChoiceAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $authorize = $this->hasPackageCompletedPaymentFlash();
        $funnel = $this->get('package.funnel.funnel_factory')->create($sheet, $request->getLocale());

        if ($eventDomain->getEvent() !== $sheet->getEvent()
            || !$sheet->hasUser($this->getUser())
            || !$sheet->getPackage()->isPassable()
            || false === $authorize
            || false === $funnel->isCompleted()
        ) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        $now            = new \DateTime();
        $funnel         = $this->get('package.funnel.funnel_factory')->create($sheet, $request->getLocale());
        $total          = $this->get('payment.total_to_pay')->getTotal($sheet);
        $depositAllowed = DepositApplicable::isApplicable($eventDomain->getEvent(), $now, $total);
        $deposit        = DepositApplicable::calculateDeposit($eventDomain->getEvent(), $now, $total);

        //Create order from cart and redirect if total payment is negative or zero
        if ($total <= 0) {
            $this->get('tactician.commandbus')->handle(new Create($sheet));

            return $this->redirectToRoute('event_order_list', [
                'sheet' => $sheet->getId(),
            ]);
        }

        if ($depositAllowed) {
            $paymentChoice = new ChoiceWithDeposit($sheet);
            $form          = $this->createForm(PaymentChoiceWithDepositType::class, $paymentChoice, [
            ]);
        } else {
            $paymentChoice = new Choice($sheet);
            $form          = $this->createForm(PaymentChoiceType::class, $paymentChoice, [
            ]);
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($paymentChoice);
                $this->consumePackageCompletedPaymentFlash();

                // This will have to redirect to payment when needed
                return $this->redirectToRoute('event_order_list', [
                    'sheet' => $sheet->getId(),
                ]);
            } catch (DepositNotAvailableException $exception) {
                $this->addFlash('error', 'flash.payment.deposit.notAvailable');

                return $this->redirectToRoute('event_package_payment', [
                    'sheet' => $sheet->getId(),
                ]);
            }
        }

        return $this->render('EventBundle:Payment:choice.html.twig', [
            'event'   => $eventDomain->getEvent(),
            'form'    => $form->createView(),
            'total'   => $total,
            'deposit' => $deposit,
            'view'    => [ 'funnel' => $funnel]
        ]);
    }

    /**
     * @return bool
     */
    private function hasPackageCompletedPaymentFlash()
    {
        $sheet = $this->consumePackageCompletedPaymentFlash();

        if (!empty($sheet)) {
            $this->addFlash('package_completed_payment', $sheet);
        }

        return !empty($sheet);
    }

    /**
     * @return null|int
     */
    private function consumePackageCompletedPaymentFlash()
    {
        $sheet = $this->get('session')->getFlashBag()->get('package_completed_payment');

        return $sheet;
    }
}
