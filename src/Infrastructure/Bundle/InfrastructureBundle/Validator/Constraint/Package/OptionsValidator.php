<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
use Proximum\Vimeet\Domain\Package\Product\QuantityMinGuesser;
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
     * @param QuantityMaxGuesser $quantityMaxGuesser
     * @param QuantityMinGuesser $quantityMinGuesser
     * @param \DateTimeInterface $now
     * @param Merger             $merger
     */
    public function __construct(
        QuantityMaxGuesser $quantityMaxGuesser,
        QuantityMinGuesser $quantityMinGuesser,
        \DateTimeInterface $now,
        Merger $merger
    ) {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
        $this->quantityMinGuesser = $quantityMinGuesser;
        $this->now                = $now;
        $this->merger             = $merger;
    }

    /**
     * @param SelectOptions $selectOptions
     * @param Constraint    $constraint
     */
    public function validate($selectOptions, Constraint $constraint)
    {
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

            $quantityMax = $this->quantityMaxGuesser->getMaxByProduct($selectOptions->sheet, $options[$id]);
            $quantityMin = $this->quantityMinGuesser->getMinProduct($selectOptions->sheet, $options[$id], $quantity);

            if (false === $quantityMin) {
                $this
                    ->context
                    ->buildViolation('package.product.quantityMinPromotionCode')
                    ->atPath($id)
                    ->addViolation();
            }

            if ($quantity < $quantityMin || $quantity > $quantityMax) {
                $this
                    ->context
                    ->buildViolation('package.product.quantityNotMatch')
                    ->setParameters(['%min%' => 0, '%max%' => $quantityMax])
                    ->atPath($id)
                    ->addViolation();
            }

            if (isset($order) && !$options[$id]->isDeletable($this->now)) {
                if($orderRow = $order->getRowForProduct($options[$id])) {
                    if($quantity < $orderRow->getQuantity()) {
                        $this
                            ->context
                            ->buildViolation('package.product.productNotDeletable')
                            ->atPath($id)
                            ->addViolation();
                    }
                }
            }
        }
    }
}
