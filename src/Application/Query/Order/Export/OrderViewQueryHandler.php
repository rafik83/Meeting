<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Order\OrderVat\VatListViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\VatListViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\OrderView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;

class OrderViewQueryHandler
{
    /** @var BillingInfoViewQueryHandler */
    private $billingInfoViewQueryHandler;

    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesserCache;

    /** @var ProductBoughtViewQueryHandler */
    private $productBoughtViewQueryHandler;

    /** @var CustomRowBoughtViewQueryHandler */
    private $customRowBoughtViewQueryHandler;

    /** @var PromotionCodeBoughtViewQueryHandler */
    private $promotionCodeBoughtViewQueryHandler;

    /** @var VatListViewQueryHandler */
    private $vatListViewQueryHandler;

    /** @var VatApplicable */
    private $vatApplicable;

    /**
     * @param SheetInfoGuesserCache               $sheetInfoGuesserCache
     * @param BillingInfoViewQueryHandler         $billingInfoViewQueryHandler
     * @param ProductBoughtViewQueryHandler       $productBoughtViewQueryHandler
     * @param CustomRowBoughtViewQueryHandler     $customRowBoughtViewQueryHandler
     * @param PromotionCodeBoughtViewQueryHandler $promotionCodeBoughtViewQueryHandler
     * @param VatListViewQueryHandler             $vatListViewQueryHandler
     * @param VatApplicable                       $vatApplicable
     */
    public function __construct(
        SheetInfoGuesserCache $sheetInfoGuesserCache,
        BillingInfoViewQueryHandler $billingInfoViewQueryHandler,
        ProductBoughtViewQueryHandler $productBoughtViewQueryHandler,
        CustomRowBoughtViewQueryHandler $customRowBoughtViewQueryHandler,
        PromotionCodeBoughtViewQueryHandler $promotionCodeBoughtViewQueryHandler,
        VatListViewQueryHandler $vatListViewQueryHandler,
        VatApplicable $vatApplicable
    ) {
        $this->sheetInfoGuesserCache           = $sheetInfoGuesserCache;
        $this->billingInfoViewQueryHandler     = $billingInfoViewQueryHandler;
        $this->productBoughtViewQueryHandler   = $productBoughtViewQueryHandler;
        $this->customRowBoughtViewQueryHandler = $customRowBoughtViewQueryHandler;
        $this->promotionCodeBoughtViewQueryHandler = $promotionCodeBoughtViewQueryHandler;
        $this->vatListViewQueryHandler = $vatListViewQueryHandler;
        $this->vatApplicable = $vatApplicable;
    }

    /**
     * @param Event $event
     */
    public function preloadBillingInfo(Event $event)
    {
        $this->billingInfoViewQueryHandler->preload($event);
    }

    /**
     * @param OrderViewQuery $query
     *
     * @return OrderView
     */
    public function handle(OrderViewQuery $query): OrderView
    {
        $adminLocale     = $query->adminLocale;
        $sheet           = $query->order->getSheet();
        $billingInfoView = $this->billingInfoViewQueryHandler->handle(new BillingInfoViewQuery($sheet, $adminLocale));

        $productBoughtViews       = [];
        $promotionCodeBoughtViews = [];
        $customRowViews           = [];

        foreach ($query->order->getRows() as $row) {
            if ($row->isProduct()) {
                $productBoughtViews[] = $this->productBoughtViewQueryHandler->handle(new ProductBoughtViewQuery($row));
            } else {
                $customRowViews[] = $this->customRowBoughtViewQueryHandler->handle(new CustomRowBoughtViewQuery($row));
            }
        }

        foreach ($query->order->getPromotionCodes() as $promotionCodeBought) {
            $promotionCodeBoughtViews[] = $this->promotionCodeBoughtViewQueryHandler->handle(new PromotionCodeBoughtViewQuery($promotionCodeBought));
        }

        $invoiceNumber = '';
        $invoiceDate   = '';
        $formatter     = [];

        if ($query->order->hasInvoice()) {
            $eventId = $query->event->getId();

            if (!isset($formatter[$eventId])) {
                $formatter[$eventId] = \IntlDateFormatter::create(
                    $query->locale,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE,
                    $query->event->getTimeZone()
                );
            }

            $invoiceNumber = $query->order->getInvoice()->getNumber();
            $invoiceDate   = $formatter[$eventId]->format($query->order->getInvoice()->getCreatedAt());
        }

        $formatterOrderDate = \IntlDateFormatter::create(
            $query->locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $query->event->getTimeZone()
        );

        $orderDate = $formatterOrderDate->format($query->order->getCreatedAt());

        $vatListView = $this->vatListViewQueryHandler->handle(
            new VatListViewQuery(
                $query->order,
                $this->vatApplicable->isApplicable(
                    $query->event->getMode(),
                    $query->event->getCountry(),
                    $billingInfoView->countryCode,
                    $billingInfoView->vatNumber
                )
            )
        );

        return new OrderView(
            $query->order->getId(),
            $orderDate,
            $sheet->getId(),
            $this->sheetInfoGuesserCache->guessSheetTitle($sheet, $query->locale),
            $invoiceNumber,
            $invoiceDate,
            $billingInfoView,
            AmountFormatter::centsToDecimalAmount($vatListView->total),
            AmountFormatter::centsToDecimalAmount($vatListView->getVatAmount()),
            AmountFormatter::centsToDecimalAmount($vatListView->totalWithVat),
            $productBoughtViews,
            $promotionCodeBoughtViews,
            $customRowViews
        );
    }
}
