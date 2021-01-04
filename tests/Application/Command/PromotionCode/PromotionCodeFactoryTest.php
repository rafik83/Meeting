<?php

namespace Proximum\Vimeet\Tests\Application\Command\PromotionCode;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\PromotionCode\PromotionCodeFactory;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Domain\Promotion\Exception\NonUniqueCodeException;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PromotionCodeFactoryTest extends TestCase
{
    public function testNonUniqueCodeException()
    {
        $this->expectException(NonUniqueCodeException::class);

        $event = EventFactory::createEvent();

        $promotionCode = new PromotionCode(
            $event,
            'promotionCodeTitle',
            'TESTCODE',
            10
        );

        $uniqueCodeChecker = $this->prophesize(UniqueCodeChecker::class);
        $uniqueCodeChecker->hasUniqueCode($promotionCode)->shouldBeCalled()->willReturn(false);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $promotionCodeFactory = new PromotionCodeFactory(
            $orderRepository->reveal(),
            $uniqueCodeChecker->reveal()
        );
        $promotionCodeFactory->create($event, 'promotionCodeTitle', 'TESTCODE', 10, null, [], []);
    }

    public function testTranslate()
    {
        $event = EventFactory::createEvent();

        $expectedPromotionCode = new PromotionCode(
            $event,
            'promotionCodeTitle',
            'TESTCODE',
            10
        );
        $expectedPromotionCode->translate('fr', 'label fr', 'description fr');
        $expectedPromotionCode->translate('en', 'label en', 'description en');

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        $uniqueCodeChecker = $this->prophesize(UniqueCodeChecker::class);
        $uniqueCodeChecker
            ->hasUniqueCode(Argument::type(PromotionCode::class))
            ->shouldBeCalled()
            ->willReturn(true);

        $promotionCodeFactory = new PromotionCodeFactory(
            $orderRepository->reveal(),
            $uniqueCodeChecker->reveal()
        );

        $this->assertEquals(
            $expectedPromotionCode,
            $promotionCodeFactory->create(
                $event,
                'promotionCodeTitle',
                'TESTCODE',
                10,
                null,
                [
                    'fr' => [
                        'label' => 'label fr',
                        'description' => 'description fr',
                    ],
                    'en' => [
                        'label' => 'label en',
                        'description' => 'description en',
                    ],
                ],
                []
            )
        );
    }

    public function testUpdate()
    {
        $event = EventFactory::createEvent();

        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);

        $inputPromotionCode = new PromotionCode(
            $event,
            'promotionCodeTitle',
            'TESTCODE',
            10
        );
        $inputPromotionCode->translate('fr', 'label fr', 'description fr');
        $inputPromotionCode->translate('en', 'label en', 'description en');
        $inputPromotionCode->setPromotion($product1->reveal(), Promotion::TYPE_PERCENT_OFF, 50);

        $expectedPromotionCode = new PromotionCode(
            $event,
            'Updated title',
            'UPDATED-CODE',
            5
        );
        $expectedPromotionCode->translate('fr', 'updated label fr', 'updated description fr');
        $expectedPromotionCode->translate('en', 'updated label en', 'updated description en');
        $expectedPromotionCode->setPromotion($product2->reveal(), Promotion::TYPE_PERCENT_OFF, 30, 10);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->hasOrderWithPromotionCode($inputPromotionCode)->shouldBeCalled()->willReturn(false);

        $uniqueCodeChecker = $this->prophesize(UniqueCodeChecker::class);
        $uniqueCodeChecker
            ->hasUniqueCode($inputPromotionCode)
            ->shouldBeCalled()
            ->willReturn(true);

        $promotionCodeFactory = new PromotionCodeFactory(
            $orderRepository->reveal(),
            $uniqueCodeChecker->reveal()
        );

        $result = $promotionCodeFactory->update(
            $inputPromotionCode,
            'Updated title',
            'UPDATED-CODE',
            5,
            new \DateTime('2019-07-04'),
            [
                'fr' => [
                    'label' => 'updated label fr',
                    'description' => 'updated description fr',
                ],
                'en' => [
                    'label' => 'updated label en',
                    'description' => 'updated description en',
                ],
            ],
            [
                [
                    'product' => $product2->reveal(),
                    'type' => Promotion::TYPE_PERCENT_OFF,
                    'value' => 30,
                    'quantityMax' => 10,
                ]
            ]
        );

        $promotions = $result->getPromotions();
        $promotion = reset($promotions);
        $this->assertEquals('Updated title', $result->getTitle());
        $this->assertEquals('UPDATED-CODE', $result->getCode());
        $this->assertEquals(new \DateTime('2019-07-04'), $result->getValidUntil());
        $this->assertEquals($product2->reveal(), $promotion->getProduct());
        $this->assertEquals(
            [
                'fr' => [
                    'label' => 'updated label fr',
                    'description' => 'updated description fr',
                ],
                'en' => [
                    'label' => 'updated label en',
                    'description' => 'updated description en',
                ],
            ],
            $result->getTranslationsData()
        );
    }

    public function testNoUpdatablePromotions()
    {
        $event = EventFactory::createEvent();

        $product1 = $this->prophesize(Product::class);
        $product2 = $this->prophesize(Product::class);

        $inputPromotionCode = new PromotionCode(
            $event,
            'promotionCodeTitle',
            'TESTCODE',
            10
        );
        $inputPromotionCode->setPromotion($product1->reveal(), Promotion::TYPE_PERCENT_OFF, 50);

        $expectedPromotionCode = new PromotionCode(
            $event,
            'Updated title',
            'UPDATED-CODE',
            5
        );
        $expectedPromotionCode->setPromotion($product1->reveal(), Promotion::TYPE_PERCENT_OFF, 50);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->hasOrderWithPromotionCode($inputPromotionCode)->shouldBeCalled()->willReturn(true);

        $uniqueCodeChecker = $this->prophesize(UniqueCodeChecker::class);
        $uniqueCodeChecker
            ->hasUniqueCode($inputPromotionCode)
            ->shouldBeCalled()
            ->willReturn(true);

        $promotionCodeFactory = new PromotionCodeFactory(
            $orderRepository->reveal(),
            $uniqueCodeChecker->reveal()
        );

        $result = $promotionCodeFactory->update(
            $inputPromotionCode,
            'Updated title',
            'UPDATED-CODE',
            5,
            null,
            [],
            [
                [
                    'product' => $product2->reveal(),
                    'type' => Promotion::TYPE_PERCENT_OFF,
                    'value' => 30,
                    'quantityMax' => 10,
                ]
            ]
        );

        $promotions = $result->getPromotions();
        $promotion = reset($promotions);
        $this->assertEquals('Updated title', $result->getTitle());
        $this->assertEquals('UPDATED-CODE', $result->getCode());
        $this->assertEquals($product1->reveal(), $promotion->getProduct());
    }
}
