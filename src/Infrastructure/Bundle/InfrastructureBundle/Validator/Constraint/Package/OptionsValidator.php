<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Model\Product;
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
     * @param QuantityMaxGuesser $quantityMaxGuesser
     * @param QuantityMinGuesser $quantityMinGuesser
     * @param \DateTimeInterface $now
     */
    public function __construct(
        QuantityMaxGuesser $quantityMaxGuesser,
        QuantityMinGuesser $quantityMinGuesser,
        \DateTimeInterface $now
    ) {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
        $this->quantityMinGuesser = $quantityMinGuesser;
        $this->now                = $now;
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
        }
    }
}
