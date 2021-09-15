<?php

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;

class PromotionFactory
{
    /**
     * @param string $title
     * @param string $code
     *
     * @return Promotion
     */
    public static function createPromotion($title = 'Promo Title', $code = 'PROMOCODE')
    {
        $event   = EventFactory::createEvent();
        $product = new Product($event, 'options', 'MyOptions', 'images', 1.3, 20, 2, 4, 6, true, new \DateTime(), false);

        return new Promotion(new PromotionCode($event, $title, $code), $product, 'test', 1.4);
    }
}
