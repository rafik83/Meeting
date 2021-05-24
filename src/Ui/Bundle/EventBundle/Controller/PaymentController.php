<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Order\Create;
use Proximum\Vimeet\Application\Command\Payment\Choice;
use Proximum\Vimeet\Application\Command\Payment\ChoiceWithDeposit;
use Proximum\Vimeet\Application\Command\Payment\PaymentResult;
use Proximum\Vimeet\Application\Exception\Payment\DepositNotAvailableException;
use Proximum\Vimeet\Application\Query\Package\Payment\InfoViewQuery;
use Proximum\Vimeet\Application\Query\Payment\PaymentConditionsViewQuery;
use Proximum\Vimeet\Domain\Cart\CartCleaner;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Package\Funnel\FunnelFactory;
use Proximum\Vimeet\Domain\Payment\DepositApplicable;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Payment\TotalToPay;
use Proximum\Vimeet\Infrastructure\Payum\Paypal\CapturePayment;
use Proximum\Vimeet\Infrastructure\Payum\Paypal\PreparePayment;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment\PaymentChoiceType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Payment\PaymentChoiceWithDepositType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PaymentController extends AbstractController
{
    private FunnelFactory $packageFunnelFactory;
    private CartCleaner $cartCleaner;
    private TotalToPay $paymentTotalToPay;
    private PreparePayment $preparePaypalPayment;
    private CapturePayment $capturePaypalPayment;
    private FlashBagInterface $flashBag;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        FunnelFactory $packageFunnelFactory,
        CartCleaner $cartCleaner,
        TotalToPay $paymentTotalToPay,
        PreparePayment $preparePaypalPayment,
        CapturePayment $capturePaypalPayment,
        FlashBagInterface $flashBag,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->packageFunnelFactory = $packageFunnelFactory;
        $this->cartCleaner = $cartCleaner;
        $this->paymentTotalToPay = $paymentTotalToPay;
        $this->preparePaypalPayment = $preparePaypalPayment;
        $this->capturePaypalPayment = $capturePaypalPayment;
        $this->flashBag = $flashBag;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    /**
     * @throws \Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException
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
        $funnel    = $this->packageFunnelFactory->create($sheet, $request->getLocale());
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
        $this->cartCleaner->handle($sheet);
        $total = $this->paymentTotalToPay->getTotal($sheet);

        // If nothing to pay, create the order
        if ($total <= 0) {
            $create = new Create($sheet, $user);
            $this->commandBus->handle($create);

            return $this->redirectToRoute('event_order_list', [
                'sheet' => $sheet->getId(),
            ]);
        }

        $paymentConditionsView = $this->queryBus->handle(new PaymentConditionsViewQuery($sheet));
        $depositAllowed = DepositApplicable::isApplicable($paymentConditionsView, $now, $total);
        $deposit        = DepositApplicable::calculateDeposit($paymentConditionsView, $now, $total);

        //Create order from cart and redirect if total payment is negative or zero
        if ($total <= 0) {
            $this->commandBus->handle(new Create($sheet, $user));

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

        // Create order if only CCIP payment is available and redirect
        if (count($paymentConditionsView->paymentModes) === 1
            && array_values($paymentConditionsView->paymentModes)[0] === Mode::PAYMENT_CCIP) {
            $paymentChoice->mode = Mode::PAYMENT_CCIP;
            /** @var PaymentResult $paymentResult */
            $paymentResult = $this->commandBus->handle($paymentChoice);
            $this->consumePackageCompletedPaymentFlash();

            return $this->redirectToRoute('event_order_ccip_payment', [
                'sheet' => $sheet->getId(),
                'transaction' => $paymentResult->transaction->getId(),
            ]);
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                /** @var PaymentResult $paymentResult */
                $paymentResult = $this->commandBus->handle($paymentChoice);
                $transaction = $paymentResult->transaction;

                $this->consumePackageCompletedPaymentFlash();

                if ($transaction->isPaypal()) {
                    return $this->redirectToRoute('event_package_payment_prepare_paypal', [
                        'sheet'       => $sheet->getId(),
                        'transaction' => $transaction->getId(),
                    ]);
                }

                if ($transaction->isCCIP()) {
                    return $this->redirectToRoute('event_order_ccip_payment', [
                        'sheet' => $sheet,
                        'transaction' => $paymentResult->transaction->getId(),
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

        $paymentInfoView = $this->queryBus->handle(new InfoViewQuery($sheet, $request->getLocale()));

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

    public function preparePaypalAction(
        Request $request,
        Sheet $sheet,
        Transaction $transaction
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $captureToken = $this->preparePaypalPayment->process($transaction, $request->getLocale());

        return $this->redirect($captureToken->getTargetUrl());
    }

    public function donePaymentAction(Request $request, Sheet $sheet): Response
    {
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $status = $this->capturePaypalPayment->process($request);

        $this->addFlash(
            CapturePayment::STATUS_SUCCESS === $status ? 'success' : 'error',
            sprintf('flash.payment.%s', $status)
        );

        return $this->redirectToRoute('event_order_list', ['sheet' => $sheet->getId()]);
    }

    private function hasPackageCompletedPaymentFlash(): bool
    {
        $sheetId = $this->consumePackageCompletedPaymentFlash();

        if (!empty($sheetId)) {
            $this->addFlash('package_completed_payment', $sheetId);
        }

        return !empty($sheetId);
    }

    private function consumePackageCompletedPaymentFlash(): ?int
    {
        $sheetId = $this->flashBag->get('package_completed_payment');

        return array_pop($sheetId);
    }
}
