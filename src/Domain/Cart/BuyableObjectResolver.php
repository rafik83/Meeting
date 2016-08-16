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
use Proximum\Vimeet\Domain\Template\ProductInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product\IdToProductTransformer;

class BuyableObjectResolver
{
    const BUYABLE_DEFAULT_QUANTITY = 1;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var IdToProductTransformer
     */
    private $productTransformer;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var ProductInfoGuesser
     */
    private $productInfoGuesser;

    /**
     * TemplateCartManager constructor.
     *
     * @param CartManager            $cartManager
     * @param TemplateDataFactory    $templateDataFactory
     * @param IdToProductTransformer $productTransformer
     * @param ProductInfoGuesser     $productInfoGuesser
     */
    public function __construct(
        CartManager $cartManager,
        TemplateDataFactory $templateDataFactory,
        IdToProductTransformer $productTransformer,
        ProductInfoGuesser $productInfoGuesser
    ) {
        $this->cartManager         = $cartManager;
        $this->templateDataFactory = $templateDataFactory;
        $this->productTransformer  = $productTransformer;
        $this->productInfoGuesser  = $productInfoGuesser;
    }

    /**
     * @param Sheet        $sheet
     * @param TemplateData $templateData
     */
    public function updateCart(Sheet $sheet, TemplateData $templateData)
    {
        $cart = $this->cartManager->getCart($sheet);

        foreach ($templateData->getImageObjects() as $image) {
            $this->addImage($image, $cart);
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
        if (!$image->getSelectedProduct()) {
            return;
        }

        if ($product = $this->productTransformer->transform($image->getSelectedProduct())) {
            if (!$cart->hasProduct($product)) {
                $cart->setProduct($product, self::BUYABLE_DEFAULT_QUANTITY);
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
            $updatedQuantity = $cartRow->getQuantity() - self::BUYABLE_DEFAULT_QUANTITY;
        } elseif ($cartRow->getQuantity() < 0) {
            $updatedQuantity = $cartRow->getQuantity() + self::BUYABLE_DEFAULT_QUANTITY;
        }

        if ($updatedQuantity === 0) {
            $cart->removeRow($cartRow);
        }

        $cartRow->setQuantity($updatedQuantity);
        $this->cartManager->save($cart);
    }
}
