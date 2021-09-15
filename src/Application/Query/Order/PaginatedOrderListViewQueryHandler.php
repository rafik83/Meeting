<?php

namespace Proximum\Vimeet\Application\Query\Order;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Order\OrderListView;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class PaginatedOrderListViewQueryHandler
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SheetInfoGuesser         $sheetInfoGuesser
     */
    public function __construct(OrderRepositoryInterface $orderRepository, SheetInfoGuesser $sheetInfoGuesser)
    {
        $this->orderRepository  = $orderRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param PaginatedOrderListViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedOrderListViewQuery $query)
    {
        $orders = $this->orderRepository->findAndPaginateByEvent(
            $query->event,
            $query->filters,
            $query->page,
            $query->limit
        );

        $orders->results = array_map(
            function (Order $order) use ($query) {
                return new OrderListView(
                    $order->getId(),
                    $order->getNumero(),
                    $order->getSheet()->getId(),
                    $this->sheetInfoGuesser->guessSheetTitle($order->getSheet(), $query->locale),
                    $order->getSheet()->getType()->getTitle($query->locale),
                    $order->getSheet()->getFollower() ? $order->getSheet()->getFollower()->getDisplayName() : '',
                    $order->getCreatedAt(),
                    $order->getTotalWithoutVat(),
                    $order->getVatMode(),
                    $order->getCurrency(),
                    $order->hasInvoice(),
                    $order->isCancelled()
                );
            },
            $orders->results
        );

        return $orders;
    }
}
