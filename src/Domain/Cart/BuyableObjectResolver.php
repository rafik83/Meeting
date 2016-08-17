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
use Proximum\Vimeet\Domain\Template\ProductInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
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
    private $productInfoGuesser;

    /**
     * @var Merger
     */
    private $orderMerger;

    /**
     * TemplateCartManager constructor.
     *
     * @param CartManager            $cartManager
     * @param IdToProductTransformer $productTransformer
     * @param ProductInfoGuesser     $productInfoGuesser
     * @param Merger                 $orderMerger
     */
    public function __construct(
        CartManager $cartManager,
        IdToProductTransformer $productTransformer,
        ProductInfoGuesser $productInfoGuesser,
        Merger $orderMerger
    ) {
        $this->cartManager        = $cartManager;
        $this->productTransformer = $productTransformer;
        $this->productInfoGuesser = $productInfoGuesser;
        $this->orderMerger        = $orderMerger;
    }

    /**
     * @param Sheet        $sheet
     * @param TemplateData $templateData
     */
    public function updateCart(Sheet $sheet, TemplateData $templateData)
    {
        $cart = $this->cartManager->getCart($sheet);

        foreach ($templateData->getImageObjects() as $image) {
            if ($image->getSelectedProduct()) {
                $this->addImage($image, $cart);
            }
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
            $linkedProduct = $this->productInfoGuesser->guessProduct(
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
     * @param Image $image
     * @param Cart  $cart
     */
    public function addImage(Image $image, Cart $cart)
    {
        $plan        = null;
        $orderMerged = null;

        if ($cart->getSheet()->hasOrders()) {
            $orderMerged = $this->orderMerger->merge($cart->getSheet()->getOrders());
            $plan        = $orderMerged->getPlan();
        }

        if ($product = $this->productTransformer->transform($image->getSelectedProduct())) {
            // handle product included
            if (null !== $plan && $plan->getIncludedProduct($product)) {
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
     * @param Sheet $sheet
     * @param Image $image
     */
    public function removeImage(Sheet $sheet, Image $image)
    {
        if (!$image->getSelectedProduct()) {
            return;
        }

        $cart    = $this->cartManager->getCart($sheet);
        $product = $this->productTransformer->transform($image->getSelectedProduct());
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
}
