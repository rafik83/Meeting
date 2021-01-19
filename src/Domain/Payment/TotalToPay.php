<?php

namespace Proximum\Vimeet\Domain\Payment;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;

class TotalToPay
{
    /** @var CartManager */
    private $cartManager;

    /** @var VatApplicable */
    private $vatApplicable;

    /**
     * @param CartManager   $cartManager
     * @param VatApplicable $vatApplicable
     */
    public function __construct(CartManager $cartManager, VatApplicable $vatApplicable)
    {
        $this->cartManager   = $cartManager;
        $this->vatApplicable = $vatApplicable;
    }

    /**
     * @param Sheet $sheet
     *
     * @throws MissingBillingInfoException
     *
     * @return float
     */
    public function getTotal(Sheet $sheet): float
    {
        $cart          = $this->cartManager->getCart($sheet);
        $vatApplicable = $this->vatApplicable->onSheet($sheet);
        $total         = $cart->getTotal() + $cart->getTotalDiscount();
        $vatToPay      = 0;

        if ($total < 0) {
            return 0;
        }

        if ($vatApplicable) {
            foreach ($cart->getRows() as $row) {
                if (0 === $row->getProduct()->getVat()) {
                    continue;
                }

                $vatToPay += ($row->getProduct()->getUnitPrice() * $row->getQuantity() * $row->getProduct()->getVat()) / 100;
            }

            foreach ($cart->getPromotionCodeRows() as $promotionCodeRow) {
                foreach ($promotionCodeRow->getPromotionCode()->getPromotions() as $promotion) {
                    $product = $promotion->getProduct();
                    $discount = $cart->getDiscountForProduct($promotionCodeRow->getPromotionCode(), $product);

                    if (0 === $discount) {
                        continue;
                    }

                    $vatToPay += ($discount  * $product->getVat()) / 100;
                }
            }
        }

        return $total + $vatToPay;
    }
}
