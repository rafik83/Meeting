<?php

namespace Proximum\Vimeet\Application\View\Order\Export;

class OrdersExportView
{
    /** @var ProductView[] */
    public $products;

    /** @var PromotionCodeView[] */
    public $promotionCodes;

    /** @var OrderView[] */
    public $orders;

    /** @var CustomRowView[] */
    public $customRowsColumns;

    /** @var SharedColumnsTranslationView */
    public $sharedColumnsTranslationView;

    /**
     * @param SharedColumnsTranslationView $sharedColumnsTranslationView
     * @param ProductView[]                $products
     * @param PromotionCodeView[]          $promotionCodes
     * @param OrderView[]                  $orders
     * @param CustomRowView[]              $customRowsColumns
     */
    public function __construct(
        SharedColumnsTranslationView $sharedColumnsTranslationView,
        array $products,
        array $promotionCodes,
        array $orders,
        array $customRowsColumns
    ) {
        $this->products                     = $products;
        $this->promotionCodes               = $promotionCodes;
        $this->orders                       = $orders;
        $this->customRowsColumns            = $customRowsColumns;
        $this->sharedColumnsTranslationView = $sharedColumnsTranslationView;
    }
}
