<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
use Proximum\Vimeet\Domain\Package\Product\QuantityMinGuesser;
use Proximum\Vimeet\Domain\Template\ProductInfoGuesser;
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
     * @var ProductInfoGuesser
     */
    private $productInfoGuesser;

    /**
     * @param QuantityMaxGuesser $quantityMaxGuesser
     * @param QuantityMinGuesser $quantityMinGuesser
     * @param ProductInfoGuesser $productInfoGuesser
     * @param \DateTimeInterface $now
     * @param Merger             $merger
     */
    public function __construct(
        QuantityMaxGuesser $quantityMaxGuesser,
        QuantityMinGuesser $quantityMinGuesser,
        ProductInfoGuesser $productInfoGuesser,
        \DateTimeInterface $now,
        Merger $merger
    ) {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
        $this->quantityMinGuesser = $quantityMinGuesser;
        $this->now                = $now;
        $this->merger             = $merger;
        $this->productInfoGuesser = $productInfoGuesser;
    }

    /**
     * @param SelectOptions $selectOptions
     * @param Constraint    $constraint
     */
    public function validate($selectOptions, Constraint $constraint)
    {
        $order   = null;
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

        if ($selectOptions->sheet->hasOrders()) {
            $order = $this->merger->merge($selectOptions->sheet->getOrders());
        }

        foreach ($selectOptions->options as $id => $quantity) {
            if (!isset($options[$id])) {
                $this
                    ->context
                    ->buildViolation('package.product.notAvailable')
                    ->addViolation();

                continue;
            }

            $quantityMin = $this->getQuantityMin($selectOptions->sheet, $selectOptions->locale, $options, $id,
                (int)$quantity, $order);
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
     * @param Sheet  $sheet
     * @param string $locale
     * @param int    $options
     * @param int    $id
     * @param int    $quantity
     * @param Order  $order
     *
     * @return false|int
     */
    private function getQuantityMin(Sheet $sheet, $locale, $options, $id, $quantity, Order $order = null)
    {
        $quantityMin = 0;

        $linkedProduct = $this->productInfoGuesser->guessProduct(
            $sheet,
            $options[$id],
            $locale
        );

        if (null !== $linkedProduct) {
            $quantity = $this->resolveQuantityMin($quantity, $linkedProduct, $order);

            if ($quantity < BuyableObjectResolver::PAYABLE_OPTION_QUANTITY) {
                $this
                    ->context
                    ->buildViolation('package.product.quantityMinPayableOption')
                    ->atPath($id)
                    ->addViolation();
            }
        } else {
            $quantityMin = $this->quantityMinGuesser->getMinProduct(
                $sheet,
                $options[$id],
                $quantity
            );
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
     * @param int   $options
     * @param int   $id
     *
     * @return int
     */
    private function getQuantityMax(Sheet $sheet, $options, $id)
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
     * @param Order   $order
     *
     * @return int
     */
    private function resolveQuantityMin($quantity, Product $linkedProduct, Order $order = null)
    {
        if (null !== $order && $plan = $order->getPlan()) {
            $includedProduct = $plan->getIncludedProduct($linkedProduct);
            if (null !== $includedProduct) {
                $quantity = $quantity + $includedProduct->getQuantity();
            }
        }

        return $quantity;
    }
}
