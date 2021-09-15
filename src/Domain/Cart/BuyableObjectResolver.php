<?php

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product\IdToProductTransformer;

class BuyableObjectResolver
{
    public const PAYABLE_OPTION_QUANTITY = 1;

    /** @var CartManager */
    private $cartManager;

    /** @var IdToProductTransformer */
    private $productTransformer;

    /** @var TemplateProductGuesser */
    private $templateProductGuesser;

    /** @var Merger */
    private $orderMerger;

    public function __construct(
        CartManager $cartManager,
        IdToProductTransformer $productTransformer,
        TemplateProductGuesser $templateProductGuesser,
        Merger $orderMerger
    ) {
        $this->cartManager            = $cartManager;
        $this->productTransformer     = $productTransformer;
        $this->templateProductGuesser = $templateProductGuesser;
        $this->orderMerger            = $orderMerger;
    }

    public function updateCart(Sheet $sheet, TemplateObject $object): void
    {
        $cart  = $this->cartManager->getCart($sheet);
        $order = null;

        if ($sheet->hasNotCancelledOrders()) {
            $order = $this->orderMerger->merge($sheet->getNotCancelledOrders());
        }

        if (null === $object->getSelectedProduct()) {
            return;
        }

        if ($object instanceof TemplateObject\Image
            || $object instanceof TemplateObject\MediaCollection
            || $object instanceof TemplateObject\Video
        ) {
            $this->addPayableProduct($object, $cart, $order);
        }

        $this->cartManager->save($cart);
    }

    /**
     * Check if plan has included option and update cart if needed
     *
     * @param Sheet   $sheet
     * @param Product $plan
     */
    public function resolvePlan(Sheet $sheet, Product $plan): void
    {
        $cart = $this->cartManager->getCart($sheet);

        foreach ($plan->getIncludedOptionProduct() as $optionIncluded) {
            // get product in template data
            $linkedProduct = $this->templateProductGuesser->guessProduct($sheet, $optionIncluded->getIncluded());

            if (null === $linkedProduct) {
                continue;
            }

            $cartRow = $cart->getCartRowForProduct($linkedProduct);

            if (null !== $cartRow) {
                $quantity = $cartRow->getQuantity() - $optionIncluded->getQuantity();

                if ($quantity <= 0) {
                    $cart->removeRow($cartRow);
                } else {
                    $cartRow->setQuantity($quantity);
                }
            }
        }

        $this->cartManager->save($cart);
    }

    /**
     * Look in all template objects and update product in cart if needed
     *
     * @param Sheet $sheet
     */
    public function resolveTemplate(Sheet $sheet): void
    {
        $objects = $this->templateProductGuesser->getBuyableObjects($sheet);

        foreach ($objects as $object) {
            $this->updateCart($sheet, $object);
        }
    }

    /**
     * @param TemplateObject $object
     * @param Cart           $cart
     * @param null|Order     $orderMerged
     */
    public function addPayableProduct(TemplateObject $object, Cart $cart, Order $orderMerged = null)
    {
        if ($product = $this->productTransformer->transform($object->getSelectedProduct())) {
            // handle product included
            if ($this->hasCartPlanIncludedProduct($cart, $product, $orderMerged)) {
                return;
            }

            // handle new order
            if (null !== $orderMerged) {
                $quantity = $cart->getOrderCartQuantity($product, $orderMerged);

                if ($quantity > 0) {
                    return;
                }
            }

            $cartRow = $cart->getCartRowForProduct($product);

            if (null !== $cartRow) {
                $quantity = $cartRow->getQuantity();

                // add payable option
                if ($quantity < 1) {
                    $cartRow->setQuantity($cartRow->getQuantity() + self::PAYABLE_OPTION_QUANTITY);
                }
            } else {
                $cart->setProduct($product, self::PAYABLE_OPTION_QUANTITY);
            }
        }
    }

    /**
     * @param Sheet          $sheet
     * @param TemplateObject $object
     */
    public function removePayableProduct(Sheet $sheet, TemplateObject $object): void
    {
        if (null === $object->getSelectedProduct()) {
            return;
        }

        $cart = $this->cartManager->getCart($sheet);
        $product = $this->productTransformer->transform($object->getSelectedProduct());
        $cartRow = $cart->getCartRowForProduct($product);
        $productSeveralUse = $this->templateProductGuesser->hasSeveralUse($sheet, $product);

        // option is not in the cart
        if (null === $cartRow) {
            return;
        }

        // option is in cart and is not use multiple time
        if ($productSeveralUse) {
            return;
        }

        $updatedQuantity = 0;

        if ($cartRow->getQuantity()) {
            $updatedQuantity = $cartRow->getQuantity() - self::PAYABLE_OPTION_QUANTITY;
        } elseif ($cartRow->getQuantity() < 0) {
            $updatedQuantity = $cartRow->getQuantity() + self::PAYABLE_OPTION_QUANTITY;
        }

        if (0 === $updatedQuantity) {
            $cart->removeRow($cartRow);
        }

        $cartRow->setQuantity($updatedQuantity);
        $this->cartManager->save($cart);
    }

    /**
     * @param TemplateObject $object
     *
     * @return int|null
     */
    public function getSelectedProduct(TemplateObject $object): ?int
    {
        if (null !== $object->getSelectedProduct()) {
            return $object->getSelectedProduct();
        }

        $selectedProduct = null;
        $cart            = $this->cartManager->getCart($object->getSheet());

        foreach ($object->getBuyableProducts() as $buyableProduct) {
            if ($cartRow = $cart->getCartRowForProduct($buyableProduct)) {
                $product = $cartRow->getProduct();

                if (null !== $selectedProduct && $this->isMoreExpensive($selectedProduct, $product)) {
                    $selectedProduct = $product;
                }

                if (null === $selectedProduct) {
                    $selectedProduct = $buyableProduct;
                }
            }
        }

        return (null !== $selectedProduct) ? $selectedProduct->getId() : null;
    }

    /**
     * @param Cart       $cart
     * @param Product    $product
     * @param null|Order $orderMerged
     *
     * @return bool
     */
    private function hasCartPlanIncludedProduct(
        Cart $cart,
        Product $product,
        Order $orderMerged = null
    ): bool {
        $plan = null;

        if (null !== $orderMerged) {
            $plan = $orderMerged->getPlan();
        } elseif ($planRow = $cart->getPlanRow()) {
            $plan = $planRow->getProduct();
        }

        if (null !== $plan && $plan->getIncludedProduct($product)) {
            return true;
        }

        return false;
    }

    /**
     * @param Product $product
     * @param Product $otherProduct
     *
     * @return bool
     */
    private function isMoreExpensive(Product $product, Product $otherProduct): bool
    {
        return $product->getUnitPrice() <= $otherProduct->getUnitPrice();
    }
}
