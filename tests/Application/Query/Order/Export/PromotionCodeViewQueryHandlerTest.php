<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Order\Export\PromotionCodeViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\PromotionCodeViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\PromotionCodeView;
use Proximum\Vimeet\Domain\Model\PromotionCode;

class PromotionCodeViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $adminLocale = 'fr';
        $translator  = $this->prophesize(TranslatorInterface::class);
        $promotionCode = $this->prophesize(PromotionCode::class);
        $promotionCode->getTitle()->willReturn('promotion code title');
        $promotionCode->getId()->willReturn(8);

        $translator->trans('order.column.promotionCode.quantity', ['%promotionCodeTitle%' => 'promotion code title'], 'export', $adminLocale)->shouldBeCalled()->willReturn('promotion code title quantity');
        $translator->trans('order.column.promotionCode.total', ['%promotionCodeTitle%' => 'promotion code title'], 'export', $adminLocale)->shouldBeCalled()->willReturn('promotion code title total');

        $query = new PromotionCodeViewQuery($promotionCode->reveal(), $adminLocale);

        $handler = new PromotionCodeViewQueryHandler($translator->reveal());
        $result = $handler->handle($query);

        $expected = new PromotionCodeView(8, 'promotion code title', 'promotion code title quantity', 'promotion code title total');

        $this->assertEquals($expected, $result);
    }
}
