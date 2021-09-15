<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Order\Export\PromotionCodeBoughtViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\PromotionCodeBoughtViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\PromotionCodeBoughtView;
use Proximum\Vimeet\Domain\Model\Order\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCode as PromotionCodeModel;

class PromotionCodeBoughtViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $promotionCode = $this->prophesize(PromotionCode::class);
        $promotionCodeModel = $this->prophesize(PromotionCodeModel::class);
        $promotionCode->getPromotionCode()->willReturn($promotionCodeModel->reveal());
        $promotionCodeModel->getId()->willReturn(5);
        $promotionCode->getPrice()->willReturn(100.20);

        $query = new PromotionCodeBoughtViewQuery($promotionCode->reveal());

        $handler = new PromotionCodeBoughtViewQueryHandler();
        $result = $handler->handle($query);

        $expected = new PromotionCodeBoughtView(5, 1, 100.20);

        $this->assertEquals($expected, $result);
    }
}
