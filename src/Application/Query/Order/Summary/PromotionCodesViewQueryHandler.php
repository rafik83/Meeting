<?php

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\PromotionCodesView;

class PromotionCodesViewQueryHandler
{
    /**
     * @var PromotionCodeViewQueryHandler
     */
    private $promotionCodeViewQueryHandler;

    /**
     * @param PromotionCodeViewQueryHandler $promotionCodeViewQueryHandler
     */
    public function __construct(PromotionCodeViewQueryHandler $promotionCodeViewQueryHandler)
    {
        $this->promotionCodeViewQueryHandler = $promotionCodeViewQueryHandler;
    }

    /**
     * @param PromotionCodesViewQuery $promotionCodesViewQuery
     *
     * @return PromotionCodesView
     */
    public function handle(PromotionCodesViewQuery $promotionCodesViewQuery)
    {
        $locale             = $promotionCodesViewQuery->locale;
        $order              = $promotionCodesViewQuery->order;

        $promotionCodesView = new PromotionCodesView();

        foreach ($order->getPromotionCodes() as $promotionCode) {
            $promotionCodesView->addPromotionCode(
                $this->promotionCodeViewQueryHandler->handle(
                    new PromotionCodeViewQuery(
                        $promotionCode,
                        $locale,
                        $order
                    )
                )
            );
        }

        return $promotionCodesView;
    }
}
