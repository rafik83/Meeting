<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = EventFactory::createEvent();

        // Expected
        $promotionCode = new PromotionCode(
            $event,
            'promotionCodeTitle',
            'TESTCODE',
            10
        );

        $update = new Update($promotionCode);

        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $uniqueCodeChecker = $this->prophesize(UniqueCodeChecker::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $promotionCodeRepository->set($promotionCode)->shouldBeCalled();
        $uniqueCodeChecker->hasUniqueCode($promotionCode)->shouldBeCalled()->willReturn(true);
        $orderRepository->hasOrderWithPromotionCode($promotionCode)->shouldBeCalled()->willReturn(false);

        $handler = new UpdateHandler(
            $promotionCodeRepository->reveal(),
            $uniqueCodeChecker->reveal(),
            $orderRepository->reveal()
        );

        $handler->handle($update);
    }

    public function testHandleChanges(): void
    {
        $event = EventFactory::createEvent();
        $product = $this->prophesize(Product::class);

        $promotionCode = new PromotionCode(
            $event,
            'PromotionCodeTitle',
            'TESTCODE',
            10
        );

        $update = new Update($promotionCode);
        $update->code = 'TESTCODE2';
        $update->stock = 8;
        $update->translations = [
            'fr' => [
                'label' => 'labelFr',
                'description' => 'descriptionFr',
            ],
            'en' => [
                'label' => 'labelEn',
                'description' => 'descriptionEn',
            ],
        ];
        $update->promotions = [
            0 => [
                'product'     => $product->reveal(),
                'type'        => Promotion::TYPE_VALUE_OFF,
                'value'       => 200,
                'quantityMax' => 1,
            ]
        ];

        $expected = new PromotionCode($event, 'PromotionCodeTitle', 'TESTCODE2', 8);
        $expected->translate('fr', 'labelFr', 'descriptionFr');
        $expected->translate('en', 'labelEn', 'descriptionEn');
        $expected->setPromotion($product->reveal(), Promotion::TYPE_VALUE_OFF, 200, 1);

        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $uniqueCodeChecker = $this->prophesize(UniqueCodeChecker::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $promotionCodeRepository->set($expected)->shouldBeCalled();
        $uniqueCodeChecker->hasUniqueCode($promotionCode)->shouldBeCalled()->willReturn(true);
        $orderRepository->hasOrderWithPromotionCode($promotionCode)->shouldBeCalled()->willReturn(false);

        $handler = new UpdateHandler(
            $promotionCodeRepository->reveal(),
            $uniqueCodeChecker->reveal(),
            $orderRepository->reveal()
        );

        $handler->handle($update);
    }

    public function testHandleCanNotChangePromotions(): void
    {
        $event = EventFactory::createEvent();
        $product = $this->prophesize(Product::class);

        $promotionCode = new PromotionCode(
            $event,
            'PromotionCodeTitle',
            'TESTCODE',
            10
        );
        $promotionCode->setPromotion($product->reveal(), Promotion::TYPE_VALUE_OFF, 200, 1);

        $update = new Update($promotionCode);
        $update->code = 'TESTCODE2';
        $update->stock = 8;
        $update->translations = [
            'fr' => [
                'label' => 'labelFr',
                'description' => 'descriptionFr',
            ],
            'en' => [
                'label' => 'labelEn',
                'description' => 'descriptionEn',
            ],
        ];
        $update->promotions = [
            0 => [
                'product'     => $product->reveal(),
                'type'        => Promotion::TYPE_VALUE_OFF,
                'value'       => 500,
                'quantityMax' => 2,
            ]
        ];

        $expected = new PromotionCode($event, 'PromotionCodeTitle', 'TESTCODE2', 8);
        $expected->translate('fr', 'labelFr', 'descriptionFr');
        $expected->translate('en', 'labelEn', 'descriptionEn');
        // No change on the promotion because the promotionCode is used
        $expected->setPromotion($product->reveal(), Promotion::TYPE_VALUE_OFF, 200, 1);

        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $uniqueCodeChecker = $this->prophesize(UniqueCodeChecker::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $promotionCodeRepository->set($promotionCode)->shouldBeCalled();
        $uniqueCodeChecker->hasUniqueCode($promotionCode)->shouldBeCalled()->willReturn(true);
        $orderRepository->hasOrderWithPromotionCode($promotionCode)->shouldBeCalled()->willReturn(true);

        $handler = new UpdateHandler(
            $promotionCodeRepository->reveal(),
            $uniqueCodeChecker->reveal(),
            $orderRepository->reveal()
        );

        $handler->handle($update);
    }
}
