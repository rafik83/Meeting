<?php

namespace Proximum\Vimeet\Application\Query\Order;

use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Summary\GroupsViewQuery;
use Proximum\Vimeet\Application\Query\Order\Summary\GroupsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Summary\PromotionCodesViewQuery;
use Proximum\Vimeet\Application\Query\Order\Summary\PromotionCodesViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\SummaryView;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;

class SummaryQueryHandler
{
    /** @var GroupsViewQueryHandler */
    private $groupsViewQueryHandler;

    /** @var PromotionCodesViewQueryHandler */
    private $promotionCodesViewQueryHandler;

    /** @var Balance */
    private $balance;

    /** @var OrderVatViewQueryHandler */
    private $orderVatViewQueryHandler;

    /**
     * @param GroupsViewQueryHandler         $groupsViewQueryHandler
     * @param PromotionCodesViewQueryHandler $promotionCodesViewQueryHandler
     * @param Balance                        $balance
     * @param OrderVatViewQueryHandler       $orderVatViewQueryHandler
     */
    public function __construct(
        GroupsViewQueryHandler $groupsViewQueryHandler,
        PromotionCodesViewQueryHandler $promotionCodesViewQueryHandler,
        Balance $balance,
        OrderVatViewQueryHandler $orderVatViewQueryHandler
    ) {
        $this->groupsViewQueryHandler         = $groupsViewQueryHandler;
        $this->promotionCodesViewQueryHandler = $promotionCodesViewQueryHandler;
        $this->balance                        = $balance;
        $this->orderVatViewQueryHandler       = $orderVatViewQueryHandler;
    }

    /**
     * @param SummaryQuery $summaryQuery
     *
     * @throws MissingBillingInfoException
     *
     * @return SummaryView
     */
    public function handle(SummaryQuery $summaryQuery): SummaryView
    {
        $orderVatView = $this->orderVatViewQueryHandler->handle(new OrderVatViewQuery($summaryQuery->order));

        return new SummaryView(
            $this->groupsViewQueryHandler->handle(
                new GroupsViewQuery(
                    $summaryQuery->sheet,
                    $summaryQuery->order,
                    $summaryQuery->locale
                )
            ),
            $this->promotionCodesViewQueryHandler->handle(
                new PromotionCodesViewQuery(
                    $summaryQuery->order,
                    $summaryQuery->locale
                )
            ),
            $orderVatView->isVatApplicable,
            $summaryQuery->order->getVatRate(),
            $orderVatView->vatAmount,
            $orderVatView->vatMode,
            $orderVatView->totalWithoutVat,
            $orderVatView->totalWithVat,
            $orderVatView->vatListView,
            $summaryQuery->order->getCurrency(),
            $this->balance->getRemainingToPay($summaryQuery->sheet),
            $summaryQuery->sheet
        );
    }
}
