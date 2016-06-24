<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
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
     * @param QuantityMaxGuesser $quantityMaxGuesser
     * @param \DateTimeInterface $now
     */
    public function __construct(QuantityMaxGuesser $quantityMaxGuesser, \DateTimeInterface $now)
    {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
        $this->now = $now;
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

            if ($quantity < 0 || $quantity > $quantityMax) {
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
