<?php

namespace Proximum\Vimeet\Tests\Application\Query\Package\Summary;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Package\Summary\PromotionCodeQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\PromotionCodeQueryHandler;
use Proximum\Vimeet\Application\View\Package\Summary\PromotionCodesView;
use Proximum\Vimeet\Application\View\Package\Summary\PromotionCodeView;
use Proximum\Vimeet\Application\View\Package\Summary\PromotionProductRowView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class PromotionCodeQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $locale   = 'fr';
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'Package1', $dateTime);
        $package->enable(true, true, true);
        $sheet         = SheetFactory::create($event, null, $dateTime, $type);
        $product       = ProductFactory::create($event, 'option');
        $promotionCode = new PromotionCode($event, 'Promo', 'CODE10', 5);
        $promotion     = new Promotion($promotionCode, $product, Promotion::TYPE_VALUE_OFF, 20);
        $promotionCode->setPromotion($product, Promotion::TYPE_VALUE_OFF, 20);

        $package->setPlanning($product);
        $type->setPackage($package);

        $cartRow      = new CartRow($sheet, $product, 1);
        $promotionRow = new PromotionCodeRow($sheet, $promotionCode);
        $cart         = new Cart($sheet, [$cartRow], [$promotionRow]);

        // Expected
        $promotionCodeRowView = new PromotionProductRowView(
            $promotion,
            $product->getTitle($locale),
            Promotion::TYPE_VALUE_OFF,
            20,
            1,
            20,
            -20
        );

        $promotionCodeView = new PromotionCodeView(
            null,
            null,
            null,
            -20,
            $event->getCurrency(),
            $event->getMode(),
            [$promotionCodeRowView]
        );

        $expectedPromotionCodesView = new PromotionCodesView([$promotionCodeView]);

        $handler = new PromotionCodeQueryHandler();
        $query   = new PromotionCodeQuery($sheet, $cart, $locale);

        $promotionCodesView = $handler->handle($query);

        $this->assertEquals($expectedPromotionCodesView, $promotionCodesView);
    }
}
