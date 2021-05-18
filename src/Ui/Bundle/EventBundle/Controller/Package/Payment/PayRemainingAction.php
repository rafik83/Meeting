<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Package\Payment;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Payment\PaymentConditionsViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use function in_array;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PayRemainingAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var TransactionRepositoryInterface */
    private $transactionRepository;

    /** @var RouterInterface */
    private $router;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var Balance */
    private $balance;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        TransactionRepositoryInterface $transactionRepository,
        RouterInterface $router,
        Balance $balance,
        \DateTimeInterface $dateTime
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->transactionRepository = $transactionRepository;
        $this->router = $router;
        $this->dateTime = $dateTime;
        $this->balance = $balance;
    }

    public function __invoke(EventDomain $eventDomain, Sheet $sheet, UserDomain $userDomain)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        $paymentConditionsView = $this->queryBus->handle(new PaymentConditionsViewQuery($sheet));

        // TODO: manage CCIP payment
        if (!in_array(Mode::PAYMENT_PAYPAL, $paymentConditionsView->paymentModes, true)) {
            if (empty($paymentConditionsView->paymentModes)) {
                throw new AccessDeniedException('Paypal is not accessible for this sheet, and no other payment modes available');
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
