<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Package\Payment;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Payment\PaymentConditionsViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Payment\CanPayOnline;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Payment\PaymentConditionsView;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Domain\View\OrderVatView;
use Proximum\Vimeet\Infrastructure\Payum\CCIP\FindUnpaidOrders;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use function in_array;

class PayRemainingAction
{
    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;
    private QueryBusInterface $queryBus;
    private TransactionRepositoryInterface $transactionRepository;
    private RouterInterface $router;
    private Balance $balance;
    private CanPayOnline $canPayOnline;
    private FindUnpaidOrders $ccipFindUnpaidOrders;
    private DateTimeInterface $dateTime;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        TransactionRepositoryInterface $transactionRepository,
        RouterInterface $router,
        Balance $balance,
        CanPayOnline $canPayOnline,
        FindUnpaidOrders $ccipFindUnpaidOrders,
        DateTimeInterface $dateTime
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->transactionRepository = $transactionRepository;
        $this->router = $router;
        $this->balance = $balance;
        $this->canPayOnline = $canPayOnline;
        $this->ccipFindUnpaidOrders = $ccipFindUnpaidOrders;
        $this->dateTime = $dateTime;
    }

    public function __invoke(EventDomain $eventDomain, Sheet $sheet, UserDomain $userDomain)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        /** @var PaymentConditionsView */
        $paymentConditionsView = $this->queryBus->handle(new PaymentConditionsViewQuery($sheet));

        if (!$this->canPayOnline->isSatisfiedBy($paymentConditionsView->paymentModes)) {
            // offline payment
            if (empty($paymentConditionsView->paymentModes)) {
                throw new AccessDeniedException('Online payment is not available for this sheet, and no other payment mode available');
            }

            return new RedirectResponse($this->router->generate('event_payment_info', [
                'sheet' => $sheet->getId(),
            ]));
        }

        $remainingToPay = $this->balance->getRemainingToPay($sheet);

        if (0 >= $remainingToPay || !$eventDomain->getEvent()->getConfiguration()->isAllowedToPayRemaining()) {
            return new RedirectResponse($this->router->generate(
                'event_order_list',
                ['sheet' => $sheet->getId()]
            ));
        }

        // CCIP payment (should always be the sole online payment available for an event)
        if (in_array(Mode::PAYMENT_CCIP, $paymentConditionsView->paymentModes)) {
            $remainingOrders = $this->balance->getNotCancelledOrderVatViews($sheet);

            $remainingOrderIds = array_map(fn (OrderVatView $orderVatView) => $orderVatView->orderId, $remainingOrders);
            $unpaidOrderIds = $this->ccipFindUnpaidOrders->fromOrderIds($sheet, $remainingOrderIds);

            $transaction = Transaction::createForCcip(
                $sheet,
                $userDomain->getUser(),
                AmountFormatter::centsToDecimalAmount($remainingToPay),
                $this->dateTime,
                $unpaidOrderIds
            );

            $this->transactionRepository->add($transaction);

            return new RedirectResponse($this->router->generate('event_order_ccip_payment', [
                'transaction' => $transaction->getId(),
            ]));
        }

        // Paypal payment
        $transaction = Transaction::createForPaypal(
            $sheet,
            $userDomain->getUser(),
            AmountFormatter::centsToDecimalAmount($remainingToPay),
            $this->dateTime
        );

        $this->transactionRepository->add($transaction);

        return new RedirectResponse($this->router->generate('event_package_payment_prepare_paypal', [
            'sheet'       => $sheet->getId(),
            'transaction' => $transaction->getId(),
        ]));
    }
}
