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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Infrastructure\Repository\PromotionCodeRepository;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UniqueCodeCheckerTest extends TestCase
{
    /** @var  Event */
    private $event;

    /** @var  PromotionCode */
    private $promotionCodeRepository;

    public function setUp()
    {
        $this->event                   = EventFactory::createEvent();
        $this->promotionCodeRepository = $this->prophesize(PromotionCodeRepository::class);
    }

    public static function createPromotion()
    {
        $event   = EventFactory::createEvent();
        $title   = 'Title';
        $code    = 'PROMOCODE';
        $product = new Product($event, 'test', 'test', 'test', 1.3, 2, 4, 6, true, new \DateTime(), false);

        return new Promotion(new PromotionCode($event, $title, $code), $product, 'test', 1.4);
    }

    /**
     * @dataProvider provideHasUniqueCode
     */
    public function testHasUniqueCode($expected, $promotions)
    {
        $title     = 'Title';
        $code      = 'PROMOCODE';
        $promoCode = new PromotionCode($this->event, $title, $code);

        $this->promotionCodeRepository->findDuplicate($promoCode)->shouldBeCalled()->willReturn($promotions);

        $checker = new UniqueCodeChecker($this->promotionCodeRepository->reveal());

        $this->assertEquals($expected, $checker->hasUniqueCode($promoCode));
    }

    /**
     * @dataProvider  provideExist
     */
    public function testExist(bool $expected, array $promotionCodes)
    {
        $this->promotionCodeRepository->findByEventAndCode($this->event, 'code')->shouldBeCalled()->willReturn($promotionCodes);

        $checker = new UniqueCodeChecker($this->promotionCodeRepository->reveal());

        $this->assertEquals($expected, $checker->exists($this->event, 'code'));
    }

    public static function provideHasUniqueCode()
    {
        return [
            [true, []],
            [false, [self::createPromotion()]],
        ];
    }

    public function provideExist()
    {
        $promotionCode = new PromotionCode(EventFactory::createEvent(), '', '');

        return [
            [false, []],
            [true, [ $promotionCode ]],
            [true, [$promotionCode, $promotionCode]],
        ];
    }
}
