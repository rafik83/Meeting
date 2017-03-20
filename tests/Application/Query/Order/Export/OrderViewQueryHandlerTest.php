<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Order\Export\BillingInfoViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\BillingInfoViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\CustomRowBoughtViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\CustomRowBoughtViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\OrderViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\OrderViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\ProductBoughtViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\ProductBoughtViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Export\PromotionCodeBoughtViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\PromotionCodeBoughtViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\BillingInfoView;
use Proximum\Vimeet\Application\View\Order\Export\CustomRowBoughtView;
use Proximum\Vimeet\Application\View\Order\Export\OrderView;
use Proximum\Vimeet\Application\View\Order\Export\ProductBoughtView;
use Proximum\Vimeet\Application\View\Order\Export\PromotionCodeBoughtView;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;

class OrderViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $order = $this->prophesize(Order::class);
        $sheet = $this->prophesize(Sheet::class);
        $order->getId()->willReturn(2);
        $order->getSheet()->willReturn($sheet->reveal());
        $sheet->getId()->willReturn(3);
        $row1 = $this->prophesize(Order\Row::class);
        $row1->isProduct()->willReturn(true);
        $row2 = $this->prophesize(Order\Row::class);
        $row2->isProduct()->willReturn(false);
        $order->getRows()->willReturn([$row1->reveal(), $row2->reveal()]);
        $promotionCode = $this->prophesize(Order\PromotionCode::class);
        $order->getPromotionCodes()->willReturn([$promotionCode->reveal()]);

        $locale      = 'en';
        $adminLocale = 'fr';
        $sheetInfoGuesserCache               = $this->prophesize(SheetInfoGuesserCache::class);
        $billingInfoViewQueryHandler         = $this->prophesize(BillingInfoViewQueryHandler::class);
        $productBoughtViewQueryHandler       = $this->prophesize(ProductBoughtViewQueryHandler::class);
        $customRowBoughtViewQueryHandler     = $this->prophesize(CustomRowBoughtViewQueryHandler::class);
        $promotionCodeBoughtViewQueryHandler = $this->prophesize(PromotionCodeBoughtViewQueryHandler::class);

        $sheetInfoGuesserCache->guessSheetTitle($sheet->reveal(), $locale)->shouldBeCalled()->willReturn('sheet title');
        $billingInfo = new BillingInfoView('gender', 'lastName', 'firstName', 'position', 'phone', 'mobile', 'email@email.fr');

        $productBought = new ProductBoughtView(1, 2, 3, 6);
        $productBoughtViewQueryHandler->handle(new ProductBoughtViewQuery($row1->reveal()))->shouldBeCalled()->willReturn($productBought);
        $customRowBought = new CustomRowBoughtView(2, 'title', 23, 2, 46);
        $customRowBoughtViewQueryHandler->handle(new CustomRowBoughtViewQuery($row2->reveal()))->shouldBeCalled()->willReturn($customRowBought);

        $promotionCodeBought = new PromotionCodeBoughtView(2, 1, 120);
        $promotionCodeBoughtViewQueryHandler->handle(new PromotionCodeBoughtViewQuery($promotionCode->reveal()))->shouldBeCalled()->willReturn($promotionCodeBought);

        $billingInfoViewQueryHandler->handle(new BillingInfoViewQuery($sheet->reveal(), $adminLocale))->shouldBeCalled()->willReturn($billingInfo);

        $handler = new OrderViewQueryHandler(
            $sheetInfoGuesserCache->reveal(),
            $billingInfoViewQueryHandler->reveal(),
            $productBoughtViewQueryHandler->reveal(),
            $customRowBoughtViewQueryHandler->reveal(),
            $promotionCodeBoughtViewQueryHandler->reveal()
        );
        $result = $handler->handle(new OrderViewQuery($order->reveal(), $locale, $adminLocale));

        $expected = new OrderView(2, 3, 'sheet title', $billingInfo, [$productBought], [$promotionCodeBought], [$customRowBought]);

        $this->assertEquals($expected, $result);
    }
}
