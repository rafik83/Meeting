<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Promotion\Request;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Infrastructure\Repository\PromotionCodeRepository;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UniqueCodeCheckerTest extends TestCase
{
    public static function createPromotion()
    {
        $event = EventFactory::createEvent();
        $title = 'Title';
        $code = 'PROMOCODE';
        $product = new Product($event, 'test', 'test', 'test', 1.3, 2, 4, 6, true, new \DateTime(), false );

        return new Promotion(new PromotionCode($event, $title, $code), $product,'test', 1.4);
    }

    public static function provideHasUniqueCode()
    {
        return [
            [true, []],
            [false, [self::createPromotion()]],
        ];
    }

    /**
     *@dataProvider provideHasUniqueCode
     *
     * @param $expected
     * @param $promotions
     */
    public function testHasUniqueCode($expected, $promotions)
    {
        $event = EventFactory::createEvent();
        $title = 'Title';
        $code = 'PROMOCODE';
        $promoCode= new PromotionCode($event, $title, $code);

        $promotionCodeRepository = $this->prophesize(PromotionCodeRepository::class);
        $promotionCodeRepository->findDuplicate($promoCode)->shouldBeCalled()->willReturn($promotions);

        $checker = new UniqueCodeChecker($promotionCodeRepository->reveal());

        $this->assertEquals($expected, $checker->hasUniqueCode($promoCode));
    }

    public function provideExist()
    {
        return [
            [false, []],
            [true, [new PromotionCode(EventFactory::createEvent(), '', '')]],
            [true, [new PromotionCode(EventFactory::createEvent(), '', ''), new PromotionCode(EventFactory::createEvent(), '', '')]],
        ];
    }

    /**
     * @dataProvider  provideExist
     *
     * @param bool  $expected
     * @param array $promotionCodes
     */
    public function testExist(bool $expected, array $promotionCodes)
    {
        $event = EventFactory::createEvent();

        $promotionCodeRepository = $this->prophesize(PromotionCodeRepository::class);
        $promotionCodeRepository->findByEventAndCode($event, 'code')->shouldBeCalled()->willReturn($promotionCodes);

        $checker = new UniqueCodeChecker($promotionCodeRepository->reveal());

        $this->assertEquals($expected, $checker->exists($event, 'code'));
    }
}
