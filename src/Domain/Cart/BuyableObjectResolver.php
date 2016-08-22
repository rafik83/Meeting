<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Template\ProductInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product\IdToProductTransformer;

class BuyableObjectResolver
{
    const PAYABLE_OPTION_QUANTITY = 1;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var IdToProductTransformer
     */
    private $productTransformer;

    /**
     * @var ProductInfoGuesser
     */
    private $templateProductGuesser;

    /**
     * @var Merger
     */
    private $orderMerger;

    /**
     * TemplateCartManager constructor.
     *
     * @param CartManager            $cartManager
     * @param IdToProductTransformer $productTransformer
     * @param TemplateProductGuesser $templateProductGuesser
     * @param Merger                 $orderMerger
     */
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

    /**
     * @param Sheet          $sheet
     * @param TemplateObject $object
     */
    public function updateCart(Sheet $sheet, TemplateObject $object)
    {
        $cart = $this->cartManager->getCart($sheet);

        if (!$object->getSelectedProduct()) {
            return;
        }

        if ($object instanceof TemplateObject\Image ||
            $object instanceof TemplateObject\MediaCollection
        ) {
            $this->addPayableProduct($object, $cart);
        }

        $this->cartManager->save($cart);
    }

    /**
     * @param Sheet   $sheet
     * @param Product $plan
     */
    public function resolvePlan(Sheet $sheet, Product $plan)
    {
        $cart = $this->cartManager->getCart($sheet);

        foreach ($plan->getIncludedOptionProduct() as $optionIncluded) {
            // get product in template data
            $linkedProduct = $this->templateProductGuesser->guessProduct(
                $sheet,
                $optionIncluded->getIncluded(),
                'fr'
            );

            if (null === $linkedProduct) {
                continue;
            }

            $cartRow = $cart->getCartRowForProduct($linkedProduct);

            if (null !== $linkedProduct && null !== $cartRow) {
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
     * @param TemplateObject $object
     * @param Cart           $cart
     */
    public function addPayableProduct(TemplateObject $object, Cart $cart)
    {
        $orderMerged = null;

        if ($product = $this->productTransformer->transform($object->getSelectedProduct())) {

            // handle product included
            if ($this->hasCartPlanIncludedProduct($cart, $product)) {
                return;
            }

            $cartRow = $cart->getCartRowForProduct($product);

            // handle new order
            if ($orderMerged) {
                $quantity = $cart->getOrderCartQuantity($product, $orderMerged);

                if ($quantity < 1) {
                    $cartRow->setQuantity($cartRow->getQuantity() + self::PAYABLE_OPTION_QUANTITY);
                }
            } elseif (null === $cartRow) {
                // first order
                $cart->setProduct($product, self::PAYABLE_OPTION_QUANTITY);
            }
        }
    }

    /**
     * @param Sheet          $sheet
     * @param TemplateObject $object
     */
    public function removePayableProduct(Sheet $sheet, TemplateObject $object)
    {
        if (!$object->getSelectedProduct()) {
            return;
        }

        $cart    = $this->cartManager->getCart($sheet);
        $product = $this->productTransformer->transform($object->getSelectedProduct());
        $cartRow = $cart->getCartRowForProduct($product);

        if (null === $cartRow) {
            return;
        }

        $updatedQuantity = 0;

        if ($cartRow->getQuantity() > 0) {
            $updatedQuantity = $cartRow->getQuantity() - self::PAYABLE_OPTION_QUANTITY;
        } elseif ($cartRow->getQuantity() < 0) {
            $updatedQuantity = $cartRow->getQuantity() + self::PAYABLE_OPTION_QUANTITY;
        }

        if ($updatedQuantity === 0) {
            $cart->removeRow($cartRow);
        }

        $cartRow->setQuantity($updatedQuantity);
        $this->cartManager->save($cart);
    }

    /**
     * @param Cart    $cart
     * @param Product $product
     *
     * @return bool
     */
    private function hasCartPlanIncludedProduct(Cart $cart, Product $product)
    {
        $plan = null;

        if ($cart->getSheet()->hasOrders()) {
            $orderMerged = $this->orderMerger->merge($cart->getSheet()->getOrders());
            $plan        = $orderMerged->getPlan();
        } elseif ($planRow = $cart->getPlanRow()) {
            $plan = $planRow->getProduct();
        }

        if (null !== $plan && $plan->getIncludedProduct($product)) {
            return true;
        }

        return false;
    }
}
