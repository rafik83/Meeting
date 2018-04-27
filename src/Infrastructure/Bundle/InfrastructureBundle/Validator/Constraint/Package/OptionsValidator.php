<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
use Proximum\Vimeet\Domain\Package\Product\QuantityMinGuesser;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OptionsValidator extends ConstraintValidator
{
    /**
     * @var QuantityMaxGuesser
     */
    private $quantityMaxGuesser;

    /**
     * @var \DateTimeInterface
     */
    private $now;

    /**
     * @var QuantityMinGuesser
     */
    private $quantityMinGuesser;

    /**
     * @var Merger
     */
    private $merger;

    /**
     * @var TemplateProductGuesser
     */
    private $templateProductGuesser;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @param QuantityMaxGuesser     $quantityMaxGuesser
     * @param QuantityMinGuesser     $quantityMinGuesser
     * @param TemplateProductGuesser $templateProductGuesser
     * @param \DateTimeInterface     $now
     * @param Merger                 $merger
     * @param CartManager            $cartManager
     */
    public function __construct(
        QuantityMaxGuesser $quantityMaxGuesser,
        QuantityMinGuesser $quantityMinGuesser,
        TemplateProductGuesser $templateProductGuesser,
        \DateTimeInterface $now,
        Merger $merger,
        CartManager $cartManager
    ) {
        $this->quantityMaxGuesser     = $quantityMaxGuesser;
        $this->quantityMinGuesser     = $quantityMinGuesser;
        $this->now                    = $now;
        $this->merger                 = $merger;
        $this->templateProductGuesser = $templateProductGuesser;
        $this->cartManager            = $cartManager;
    }

    /**
     * @param SelectOptions $selectOptions
     * @param Constraint    $constraint
     */
    public function validate($selectOptions, Constraint $constraint)
    {
        $order   = null;
        $cart    = $this->cartManager->getCart($selectOptions->sheet);
        $options = $selectOptions->sheet->getPackage()->getAvailablesOptions($this->now);
        $options = array_combine(
            array_map(
                function (Product $product) {
                    return $product->getId();
                },
                $options
            ),
            $options
        );

        if ($selectOptions->sheet->hasNotCancelledOrders()) {
            $order = $this->merger->merge($selectOptions->sheet->getNotCancelledOrders());
        }

        foreach ($selectOptions->options as $id => $quantity) {
            if (!isset($options[$id])) {
                $this
                    ->context
                    ->buildViolation('package.product.notAvailable')
                    ->addViolation();

                continue;
            }

            $quantityMin = $this->getQuantityMin(
                $selectOptions->sheet,
                $options,
                $id,
                (int) $quantity,
                $cart,
                $order
            );

            $quantityMax = $this->getQuantityMax($selectOptions->sheet, $options, $id);

            if (isset($order) && !$options[$id]->isDeletable($this->now)) {
                if ($orderRow = $order->getRowForProduct($options[$id])) {
                    if ($quantity < $orderRow->getQuantity()) {
                        $this
                            ->context
                            ->buildViolation('package.product.productNotDeletable')
                            ->atPath($id)
                            ->addViolation();
                    }
                }
            }

            $this->validateQuantity($quantity, $quantityMin, $quantityMax, $id);
        }
    }

    /**
     * @param Sheet $sheet
     * @param array $options
     * @param int   $id
     * @param int   $quantity
     * @param Cart  $cart
     * @param Order $order
     *
     * @return int|false
     */
    private function getQuantityMin(
        Sheet $sheet,
        array $options,
        $id,
        $quantity,
        Cart $cart,
        Order $order = null
    ) {
        $linkedProduct = $this->templateProductGuesser->guessProduct(
            $sheet,
            $options[$id]
        );

        $quantityMin = $this->quantityMinGuesser->getMinProduct(
            $sheet,
            $options[$id],
            $quantity
        );

        if (null !== $linkedProduct) {
            $quantity = $this->resolveQuantityMin($quantity, $linkedProduct, $cart, $order);

            if ($quantity < BuyableObjectResolver::PAYABLE_OPTION_QUANTITY) {
                $this
                    ->context
                    ->buildViolation('package.product.quantityMinPayableOption')
                    ->atPath($id)
                    ->addViolation();
            }
        }

        if (false === $quantityMin) {
            $this
                ->context
                ->buildViolation('package.product.quantityMinPromotionCode')
                ->atPath($id)
                ->addViolation();
        }

        return $quantityMin;
    }

    /**
     * @param Sheet $sheet
     * @param array $options
     * @param int   $id
     *
     * @return int
     */
    private function getQuantityMax(Sheet $sheet, array $options, $id)
    {
        $quantityMax = $this->quantityMaxGuesser->getMaxByProduct(
            $sheet,
            $options[$id]
        );

        return $quantityMax;
    }

    /**
     * Validate minimum and maximum quantity violation
     *
     * @param int $quantity
     * @param int $quantityMin
     * @param int $quantityMax
     * @param int $fieldId
     */
    private function validateQuantity($quantity, $quantityMin, $quantityMax, $fieldId)
    {
        if ($quantity < $quantityMin || $quantity > $quantityMax) {
            $this
                ->context
                ->buildViolation('package.product.quantityNotMatch')
                ->setParameters(['%min%' => 0, '%max%' => $quantityMax])
                ->atPath($fieldId)
                ->addViolation();
        }
    }

    /**
     * Resolve minimum quantity by handling included product in plan
     *
     * @param int     $quantity
     * @param Product $linkedProduct
     * @param Cart    $cart
     * @param Order   $order
     *
     * @return int
     */
    private function resolveQuantityMin($quantity, Product $linkedProduct, Cart $cart, Order $order = null)
    {
        $plan = null;

        if (null !== $order) {
            $plan = $order->getPlan();
        } elseif ($planRow = $cart->getPlanRow()) {
            $plan = $planRow->getProduct();
        }

        if (null !== $plan) {
            $includedProduct = $plan->getIncludedProduct($linkedProduct);
            if (null !== $includedProduct) {
                $quantity = $quantity + $includedProduct->getQuantity();
            }
        }

        return $quantity;
    }
}
