<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Order\Create;
use Proximum\Vimeet\Application\Command\Payment\Choice;
use Proximum\Vimeet\Application\Command\Payment\ChoiceWithDeposit;
use Proximum\Vimeet\Application\Exception\Payment\DepositNotAvailableException;
use Proximum\Vimeet\Application\Query\Package\Payment\InfoViewQuery;
use Proximum\Vimeet\Application\Query\Payment\PaymentConditionsViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Payment\DepositApplicable;
use Proximum\Vimeet\Infrastructure\Payum\Paypal\CapturePayment;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment\PaymentChoiceType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment\PaymentChoiceWithDepositType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
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
     * @param UserDomain  $userDomain
     *
     * @throws \Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException
     *
     * @return Response
     */
    public function paymentChoiceAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        UserDomain $userDomain
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $authorize = $this->hasPackageCompletedPaymentFlash();
        $funnel    = $this->get('package.funnel.funnel_factory')->create($sheet, $request->getLocale());
        $user = $userDomain->getUser();

        if ($eventDomain->getEvent() !== $sheet->getEvent()
            || !$sheet->hasUser($user)
            || !$sheet->getPackage()->isPassable()
        ) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        if (false === $authorize || false === $funnel->isCompleted()) {
            return $this->redirectToRoute('event_order_list', ['sheet' => $sheet->getId()]);
        }

        $now   = new \DateTime();
        $this->get('cart_cleaner')->handle($sheet);
        $total = $this->get('payment.total_to_pay')->getTotal($sheet);

        // If nothing to pay, create the order
        if ($total <= 0) {
            $create = new Create($sheet, $user);
            $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('event_order_list', [
                'sheet' => $sheet->getId(),
            ]);
        }

        $paymentConditionsView = $this
            ->get('Proximum\Vimeet\Infrastructure\Adapter\QueryBus')
            ->handle(new PaymentConditionsViewQuery($sheet))
        ;
        $depositAllowed = DepositApplicable::isApplicable($paymentConditionsView, $now, $total);
        $deposit        = DepositApplicable::calculateDeposit($paymentConditionsView, $now, $total);

        //Create order from cart and redirect if total payment is negative or zero
        if ($total <= 0) {
            $this->get('tactician.commandbus')->handle(new Create($sheet, $user));

            return $this->redirectToRoute('event_order_list', [
                'sheet' => $sheet->getId(),
            ]);
        }

        if ($depositAllowed) {
            $paymentChoice = new ChoiceWithDeposit($sheet, $user);
            $form          = $this->createForm(PaymentChoiceWithDepositType::class, $paymentChoice, [
                'paymentConditionsView' => $paymentConditionsView,
            ]);
        } else {
            $paymentChoice = new Choice($sheet, $user);
            $form          = $this->createForm(PaymentChoiceType::class, $paymentChoice, [
                'paymentConditionsView' => $paymentConditionsView,
            ]);
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

        $paymentInfoView = $this->get(QueryBusInterface::class)->handle(new InfoViewQuery($sheet, $request->getLocale()));

        return $this->render('EventBundle:Payment:choice.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'form' => $form->createView(),
            'total' => $total,
            'deposit' => $deposit,
            'paymentInfoView' => $paymentInfoView,
            'view' => ['funnel' => $funnel],
        ]);
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
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

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
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

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
        $sheetId = $this->consumePackageCompletedPaymentFlash();

        if (!empty($sheetId)) {
            $this->addFlash('package_completed_payment', $sheetId);
        }

        return !empty($sheetId);
    }

    /**
     * @return null|int
     */
    private function consumePackageCompletedPaymentFlash()
    {
        $sheetId = $this->get('session')->getFlashBag()->get('package_completed_payment');

        return $sheetId;
    }
}
