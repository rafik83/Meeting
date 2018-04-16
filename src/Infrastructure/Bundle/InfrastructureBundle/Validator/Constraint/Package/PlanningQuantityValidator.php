<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
use Proximum\Vimeet\Domain\Package\Product\QuantityMinGuesser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PlanningQuantityValidator extends ConstraintValidator
{
    /**
     * @var QuantityMaxGuesser
     */
    private $quantityMaxGuesser;

    /**
     * @var QuantityMinGuesser
     */
    private $quantityMinGuesser;

    /**
     * @param QuantityMaxGuesser $quantityMaxGuesser
     * @param QuantityMinGuesser $quantityMinGuesser
     */
    public function __construct(QuantityMaxGuesser $quantityMaxGuesser,  QuantityMinGuesser $quantityMinGuesser)
    {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
        $this->quantityMinGuesser = $quantityMinGuesser;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     * @param Constraint                   $constraint
     */
    public function validate($selectParticipantAndPlanning, Constraint $constraint)
    {
        $quantity    = $selectParticipantAndPlanning->planningQuantity->getQuantity();
        $sheet       = $selectParticipantAndPlanning->sheet;
        $quantityMax = $this->quantityMaxGuesser->getMaxPlanning($sheet);

        $quantityMin = $this->quantityMinGuesser->getMinProduct(
            $selectParticipantAndPlanning->sheet,
            $sheet->getPackage()->getPlanning(),
            $quantity
        );

        if (false === $quantityMin) {
            $this
                ->context
                ->buildViolation('package.product.quantityMinPromotionCode')
                ->atPath('planningQuantity')
                ->addViolation();
        }

        if ($quantity < $quantityMin || $quantity > $quantityMax) {
            $this
                ->context
                ->buildViolation('package.planning.quantity')
                ->setParameters(['%min%' => 0, '%max%' => $quantityMax])
                ->atPath('planningQuantity')
                ->addViolation();
        }
    }
}
