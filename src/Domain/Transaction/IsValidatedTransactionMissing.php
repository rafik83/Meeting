<?php


namespace Proximum\Vimeet\Domain\Transaction;

use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class IsValidatedTransactionMissing
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var OrderVatViewQueryHandler */
    private $orderVatViewQueryHandler;

    /** @var Balance */
    private $balance;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderVatViewQueryHandler $orderVatViewQueryHandler,
        Balance $balance
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderVatViewQueryHandler = $orderVatViewQueryHandler;
        $this->balance = $balance;
    }

    public function isSatisfiedBy(Sheet $sheet): bool
    {
        if (!$sheet->getType()->isPaymentRequired()) {
            return false;
        }

        $sheetOrders = $this->orderRepository->findNotCancelledBySheet($sheet);

        if (empty($sheetOrders)) {
            return false;
        }

        /** @var Order $firstOrder */
        $firstOrder = reset($sheetOrders);

        $orderVatQuery = new OrderVatViewQuery($firstOrder);
        $orderVatView = $this->orderVatViewQueryHandler->handle($orderVatQuery);

        return $orderVatView->totalWithVat > $this->balance->getTotalPaid($sheet);
    }
}
