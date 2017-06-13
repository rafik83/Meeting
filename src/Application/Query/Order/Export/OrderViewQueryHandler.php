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
use Proximum\Vimeet\Application\View\Order\Export\OrderView;
use Proximum\Vimeet\Domain\Model\Event;

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

    /**
     * @param SheetInfoGuesserCache               $sheetInfoGuesserCache
     * @param BillingInfoViewQueryHandler         $billingInfoViewQueryHandler
     * @param ProductBoughtViewQueryHandler       $productBoughtViewQueryHandler
     * @param CustomRowBoughtViewQueryHandler     $customRowBoughtViewQueryHandler
     * @param PromotionCodeBoughtViewQueryHandler $promotionCodeBoughtViewQueryHandler
     */
    public function __construct(
        SheetInfoGuesserCache $sheetInfoGuesserCache,
        BillingInfoViewQueryHandler $billingInfoViewQueryHandler,
        ProductBoughtViewQueryHandler $productBoughtViewQueryHandler,
        CustomRowBoughtViewQueryHandler $customRowBoughtViewQueryHandler,
        PromotionCodeBoughtViewQueryHandler $promotionCodeBoughtViewQueryHandler
    ) {
        $this->sheetInfoGuesserCache           = $sheetInfoGuesserCache;
        $this->billingInfoViewQueryHandler     = $billingInfoViewQueryHandler;
        $this->productBoughtViewQueryHandler   = $productBoughtViewQueryHandler;
        $this->customRowBoughtViewQueryHandler = $customRowBoughtViewQueryHandler;
        $this->promotionCodeBoughtViewQueryHandler = $promotionCodeBoughtViewQueryHandler;
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
    public function handle(OrderViewQuery $query)
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

        $invoiceNumber   = '';
        $invoiceDate     = '';

        if ($query->order->hasInvoice()) {
            $formatter = \IntlDateFormatter::create(
                $query->locale,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::NONE,
                $query->order->getSheet()->getEvent()->getTimeZone()
            );

            $invoiceNumber = $query->order->getInvoice()->getNumber();
            $invoiceDate   = $formatter->format($query->order->getInvoice()->getCreatedAt());
        }

        return new OrderView(
            $query->order->getId(),
            $sheet->getId(),
            $this->sheetInfoGuesserCache->guessSheetTitle($sheet, $query->locale),
            $invoiceNumber,
            $invoiceDate,
            $billingInfoView,
            $productBoughtViews,
            $promotionCodeBoughtViews,
            $customRowViews
        );
    }
}
