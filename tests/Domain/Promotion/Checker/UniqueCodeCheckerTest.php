<?php

namespace Proximum\Vimeet\Tests\Domain\Promotion\Request;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Infrastructure\Repository\PromotionCodeRepository;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\PromotionFactory;

class UniqueCodeCheckerTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var PromotionCode */
    private $promotionCodeRepository;

    public function setUp()
    {
        $this->event                   = EventFactory::createEvent();
        $this->promotionCodeRepository = $this->prophesize(PromotionCodeRepository::class);
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
     * @dataProvider provideExist
     */
    public function testExist(bool $expected, array $promotionCodes)
    {
        $this->promotionCodeRepository
            ->findByEventAndCode($this->event, 'code')
            ->shouldBeCalled()
            ->willReturn($promotionCodes);

        $checker = new UniqueCodeChecker($this->promotionCodeRepository->reveal());

        $this->assertEquals($expected, $checker->exists($this->event, 'code'));
    }

    public static function provideHasUniqueCode()
    {
        return [
            [true, []],
            [false, [PromotionFactory::createPromotion()]],
        ];
    }

    public function provideExist()
    {
        $promotionCode = new PromotionCode(EventFactory::createEvent(), '', '');

        return [
            [false, []],
            [true, [$promotionCode]],
            [true, [$promotionCode, $promotionCode]],
        ];
    }
}
