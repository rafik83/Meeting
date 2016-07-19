<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Payum\Core\Request\GetHumanStatus;
use Proximum\Vimeet\Application\Command\Payment\Choice;
use Proximum\Vimeet\Application\Command\Payment\ChoiceWithDeposit;
use Proximum\Vimeet\Application\Exception\Payment\DepositNotAvailableException;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Payment\DepositApplicable;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment\PaymentChoiceType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment\PaymentChoiceWithDepositType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            'view'    => ['funnel' => $funnel]
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return RedirectResponse
     */
    public function prepareAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $gatewayName = 'offline';

        $storage = $this->get('payum')->getStorage(Payment::class);

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123); // 1.23 EUR
        $payment->setDescription('A description');
        $payment->setClientId('anId');
        $payment->setClientEmail('foo@example.com');

        $storage->update($payment);

        $captureToken = $this->get('payum')->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $payment,
            'event_package_payment_done', // the route to redirect after capture
            ['sheet' => $sheet->getId()]
        );

        return $this->redirect($captureToken->getTargetUrl());
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function doneAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $token = $this->get('payum')->getHttpRequestVerifier()->verify($request);

        $gateway = $this->get('payum')->getGateway($token->getGatewayName());

        // you can invalidate the token. The url could not be requested any more.
        // $this->get('payum')->getHttpRequestVerifier()->invalidate($token);

        // Once you have token you can get the model from the storage directly.
        //$identity = $token->getDetails();
        //$payment = $payum->getStorage($identity->getClass())->find($identity);

        // or Payum can fetch the model for you while executing a request (Preferred).
        $gateway->execute($status = new GetHumanStatus($token));
        $payment = $status->getFirstModel();

        return $this->render('EventBundle:Payment:done.html.twig', [
            'event'   => $eventDomain->getEvent(),
            'status'  => $status->getValue(),
            'payment' => [
                'total_amount'  => $payment->getTotalAmount(),
                'currency_code' => $payment->getCurrencyCode(),
                'details'       => $payment->getDetails(),
            ],
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
        $sheet = $this->container->get('session')->getFlashBag()->get('package_completed_payment');

        return $sheet;
    }
}
