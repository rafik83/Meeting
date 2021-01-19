<?php

namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsByEventQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsByEventQueryHandler;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetIdsQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetIdsQueryHandler;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewsBySheetQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Domain\View\OrderVatView;

class Balance
{
    /** @var OrderVatViewsByEventQueryHandler */
    private $orderVatViewsByEventQueryHandler;

    /** @var OrderVatViewsBySheetQueryHandler */
    private $orderVatViewsBySheetQueryHandler;

    /** @var OrderVatViewsBySheetIdsQueryHandler */
    private $orderVatViewsBySheetIdsQueryHandler;

    /** @var TransactionRepositoryInterface */
    private $transactionRepository;

    /** @var array of OrderVatView[] indexed by Sheet id */
    private $orderVatViewsBySheet = [];

    /** @var array of Transaction[] indexed by Sheet id */
    private $transactionsBySheet = [];

    /**
     * @param OrderVatViewsByEventQueryHandler    $orderVatViewsByEventQueryHandler
     * @param OrderVatViewsBySheetQueryHandler    $orderVatViewsBySheetQueryHandler
     * @param OrderVatViewsBySheetIdsQueryHandler $orderVatViewsBySheetIdsQueryHandler
     * @param TransactionRepositoryInterface      $transactionRepository
     */
    public function __construct(
        OrderVatViewsByEventQueryHandler $orderVatViewsByEventQueryHandler,
        OrderVatViewsBySheetQueryHandler $orderVatViewsBySheetQueryHandler,
        OrderVatViewsBySheetIdsQueryHandler $orderVatViewsBySheetIdsQueryHandler,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->orderVatViewsByEventQueryHandler    = $orderVatViewsByEventQueryHandler;
        $this->orderVatViewsBySheetQueryHandler    = $orderVatViewsBySheetQueryHandler;
        $this->orderVatViewsBySheetIdsQueryHandler = $orderVatViewsBySheetIdsQueryHandler;
        $this->transactionRepository               = $transactionRepository;
    }

    /**
     * @param Event $event
     */
    public function loadAllTransactions(Event $event): void
    {
        $transactions = $this->transactionRepository->findByEventAndEnabledSheets($event);

        foreach ($transactions as $transaction) {
            $this->transactionsBySheet[$transaction->getSheet()->getId()][] = $transaction;
        }
    }

    /**
     * @param Event $event
     * @param int[] $sheetIds
     */
    public function loadAllTransactionsForSheetIds(Event $event, array $sheetIds)
    {
        $transactions = $this->transactionRepository->findByEventAndSheetIds($event, $sheetIds);

        foreach ($transactions as $transaction) {
            $this->transactionsBySheet[$transaction->getSheet()->getId()][] = $transaction;
        }
    }

    /**
     * @param Event $event
     */
    public function loadAllOrderVatViews(Event $event): void
    {
        $orderVatViews = $this->orderVatViewsByEventQueryHandler->handle(new OrderVatViewsByEventQuery($event));

        foreach ($orderVatViews as $orderVatView) {
            $this->orderVatViewsBySheet[$orderVatView->sheetId][] = $orderVatView;
        }
    }

    /**
     * @param Event $event
     * @param int[] $sheetIds
     */
    public function loadAllOrderVatViewsForSheetIds(Event $event, array $sheetIds): void
    {
        $orderVatViews = $this->orderVatViewsBySheetIdsQueryHandler->handle(
            new OrderVatViewsBySheetIdsQuery($event, $sheetIds)
        );

        foreach ($orderVatViews as $orderVatView) {
            $this->orderVatViewsBySheet[$orderVatView->sheetId][] = $orderVatView;
        }
    }

    /**
     * @param Event $event
     */
    public function loadAllForEvent(Event $event): void
    {
        $this->loadAllOrderVatViews($event);
        $this->loadAllTransactions($event);
    }

    /**
     * @param Event $event
     * @param int[] $sheetIds
     */
    public function loadAllForSheetIds(Event $event, array $sheetIds): void
    {
        $this->loadAllOrderVatViewsForSheetIds($event, $sheetIds);
        $this->loadAllTransactionsForSheetIds($event, $sheetIds);
    }

    /**
     * @param Sheet $sheet
     *
     * @return OrderVatView[]
     */
    public function getOrderVatViews(Sheet $sheet)
    {
        if (!isset($this->orderVatViewsBySheet[$sheet->getId()])) {
            $this->orderVatViewsBySheet[$sheet->getId()] = $this->orderVatViewsBySheetQueryHandler->handle(
                new OrderVatViewsBySheetQuery($sheet)
            );
        }

        return $this->orderVatViewsBySheet[$sheet->getId()];
    }

    /**
     * @param Sheet $sheet
     *
     * @return OrderVatView[]
     */
    public function getNotCancelledOrderVatViews(Sheet $sheet)
    {
        $orderVatViews = $this->getOrderVatViews($sheet);

        return array_filter($orderVatViews, static function (OrderVatView $orderVatView) {
            return !$orderVatView->isCancelled;
        });
    }

    /**
     * @param Sheet $sheet
     *
     * @return Transaction[]
     */
    public function getTransactions(Sheet $sheet): array
    {
        if (!isset($this->transactionsBySheet[$sheet->getId()])) {
            $this->transactionsBySheet[$sheet->getId()] = $this->transactionRepository->findBySheet($sheet);
        }

        return $this->transactionsBySheet[$sheet->getId()];
    }

    /**
     * Get total with VAT for a sheet
     *
     * @param Sheet $sheet
     *
     * @return int amount in cents
     */
    public function getTotal(Sheet $sheet): int
    {
        $orderVatViews = $this->getNotCancelledOrderVatViews($sheet);

        $total = 0;

        foreach ($orderVatViews as $orderVatView) {
            $total += $orderVatView->totalWithVat;
        }

        return $total;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int amount in cents
     */
    public function getTotalWithoutVat(Sheet $sheet): int
    {
        $orderVatViews = $this->getNotCancelledOrderVatViews($sheet);

        $totalWithoutVat = 0;

        foreach ($orderVatViews as $orderVatView) {
            $totalWithoutVat += $orderVatView->totalWithoutVat;
        }

        return $totalWithoutVat;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int amount in cents
     */
    public function getBalance(Sheet $sheet): int
    {
        return $this->getTotal($sheet) - $this->getTotalPaid($sheet);
    }

    /**
     * @param Sheet $sheet
     *
     * @return int amount in cents
     */
    public function getRemainingToPay(Sheet $sheet): int
    {
        $remainingToPay = $this->getBalance($sheet);

        if ($remainingToPay < 0) {
            return 0;
        }

        return $remainingToPay;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int amount in cents
     */
    public function getTotalPaid(Sheet $sheet): int
    {
        $totalPaid    = 0;
        $transactions = $this->getTransactions($sheet);

        foreach ($transactions as $transaction) {
            if ($transaction->isPaid()) {
                $totalPaid += $transaction->getAmount();
            }
        }

        return AmountFormatter::decimalToCentsAmount($totalPaid);
    }

    /**
     * @return OrderVatView[]
     */
    public function getNotCancelledOrderVatViewsFromEvent(): array
    {
        if (!isset($this->orderVatViewsBySheet) || empty($this->orderVatViewsBySheet)) {
            return [];
        }

        $notCancelledOrdersFromEvent = [];

        /** @var OrderVatView[] $sheetOrderVatViews */
        foreach ($this->orderVatViewsBySheet as $sheetOrderVatViews) {
            if (is_array($sheetOrderVatViews)) {
                foreach ($sheetOrderVatViews as $orderVatView) {
                    if (!$orderVatView->isCancelled) {
                        $notCancelledOrdersFromEvent[] = $orderVatView;
                    }
                }
            }
        }

        return $notCancelledOrdersFromEvent;
    }

    /**
     * @return int amount in cents
     */
    public function getTransactionsTotalPaidForEvent(): int
    {
        $totalPaid = 0;

        /** @var Transaction[] $transactions */
        foreach ($this->transactionsBySheet as $transactions) {
            if (\is_array($transactions)) {
                foreach ($transactions as $transaction) {
                    if ($transaction->isPaid()) {
                        $totalPaid += $transaction->getAmount();
                    }
                }
            }
        }

        return AmountFormatter::decimalToCentsAmount($totalPaid);
    }

    /**
     * Get total with VAT, if applicable, for all event
     *
     * @return int amount in cents
     */
    public function getOrdersTotalForEvent(): int
    {
        $orderVatViews = $this->getNotCancelledOrderVatViewsFromEvent();

        $total = 0;

        foreach ($orderVatViews as $orderVatView) {
            $total += $orderVatView->totalWithVat;
        }

        return $total;
    }

    /**
     * Get total with VAT, if applicable, for all event
     *
     * @return int amount in cents
     */
    public function getOrdersTotalWithoutVatForEvent(): int
    {
        $orderVatViews = $this->getNotCancelledOrderVatViewsFromEvent();

        $totalWithoutVat = 0;

        foreach ($orderVatViews as $orderVatView) {
            $totalWithoutVat += $orderVatView->totalWithoutVat;
        }

        return $totalWithoutVat;
    }

    /**
     * @return int amount in cents
     */
    public function getTotalRemainingToPayForEvent(): int
    {
        $remainingToPay = $this->getOrdersTotalForEvent() - $this->getTransactionsTotalPaidForEvent();

        if (0 > $remainingToPay) {
            return 0;
        }

        return $remainingToPay;
    }
}
