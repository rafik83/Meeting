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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Payment\DepositApplicable;
use Proximum\Vimeet\Infrastructure\Payum\Paypal\CapturePayment;
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
        $funnel    = $this->get('package.funnel.funnel_factory')->create($sheet, $request->getLocale());

        if ($eventDomain->getEvent() !== $sheet->getEvent()
            || !$sheet->hasUser($this->getUser())
            || !$sheet->getPackage()->isPassable()
            || false === $authorize
            || false === $funnel->isCompleted()
        ) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        $now   = new \DateTime();
        $total = $this->get('payment.total_to_pay')->getTotal($sheet);

        // If nothing to pay, create the order
        if ($total <= 0) {
            $create = new Create($sheet);
            $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('event_order_list', [
                'sheet' => $sheet->getId(),
            ]);
        }

        $depositAllowed = DepositApplicable::isApplicable($eventDomain->getEvent(), $now, $total);
        $deposit        = DepositApplicable::calculateDeposit($eventDomain->getEvent(), $now, $total);

        if ($depositAllowed) {
            $paymentChoice = new ChoiceWithDeposit($sheet);
            $form          = $this->createForm(PaymentChoiceWithDepositType::class, $paymentChoice);
        } else {
            $paymentChoice = new Choice($sheet);
            $form          = $this->createForm(PaymentChoiceType::class, $paymentChoice);
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                /** @var Transaction $transaction */
                $transaction = $this->get('tactician.commandbus')->handle($paymentChoice);
                $this->consumePackageCompletedPaymentFlash();

                if ($transaction->isPaypal()) {
                    return $this->redirectToRoute('event_package_payment_prepare_paypal', [
                        'sheet'       => $sheet->getId(),
                        'transaction' => $transaction->getId(),
                    ]);
                }

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
    public function payRemainingAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $remainingToPay = $this->get('order.balance')->getRemainingToPay($sheet);

        if (0 >= $remainingToPay) {
            return $this->redirectToRoute('event_order_list', ['sheet' => $sheet->getId()]);
        }

        $transaction = Transaction::createForPaypal($sheet, $remainingToPay, new \DateTime());
        $this->get('repository.transaction')->add($transaction);

        return $this->redirectToRoute('event_package_payment_prepare_paypal', [
            'sheet'       => $sheet->getId(),
            'transaction' => $transaction->getId(),
        ]);
    }

    /**
     * Only for debug
     */
    public function createTempTransactionAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet
    ) {
        $this->denyAccessIfUserNotAllowed($eventDomain->getEvent(), $sheet);

        $transaction = Transaction::createForPaypal($sheet, rand(1, 200), new \DateTime());
        $this->get('repository.transaction')->add($transaction);

        return $this->redirectToRoute(
            'event_package_payment_prepare_paypal',
            ['sheet' => $sheet->getId(), 'transaction' => $transaction->getId()]
        );
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Transaction $transaction
     *
     * @return RedirectResponse
     */
    public function preparePaypalAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Transaction $transaction
    ) {
        $this->denyAccessIfUserNotAllowed($eventDomain->getEvent(), $sheet);

        $captureToken = $this->get('vimeet.payum.paypal.prepare_payment')->process($transaction, $request->getLocale());

        return $this->redirect($captureToken->getTargetUrl());
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function donePaymentAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessIfUserNotAllowed($eventDomain->getEvent(), $sheet);

        $status = $this->get('vimeet.payum.paypal.capture_payment')->process($request);

        $this->addFlash(
            CapturePayment::STATUS_SUCCESS === $status ? 'success' : 'error',
            sprintf('flash.payment.%s', $status)
        );

        return $this->redirectToRoute('event_order_list', ['sheet' => $sheet->getId()]);
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

    /**
     * @param Event $event
     * @param Sheet $sheet
     */
    private function denyAccessIfUserNotAllowed(Event $event, Sheet $sheet)
    {
        if ($event !== $sheet->getEvent() || !$sheet->hasUser($this->getUser())) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }
    }
}
