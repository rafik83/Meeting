<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\Package\Product\ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PlanningQuantityValidator extends ConstraintValidator
{
    /** @var QuantityMaxGuesser */
    private $quantityMaxGuesser;

    /** @var ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode */
    private $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode;

    public function __construct(
        QuantityMaxGuesser $quantityMaxGuesser,
        ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode
    ) {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
        $this->conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode = $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     * @param Constraint                   $constraint
     */
    public function validate($selectParticipantAndPlanning, Constraint $constraint)
    {
        $quantity = $selectParticipantAndPlanning->planningQuantity->getQuantity();
        $sheet = $selectParticipantAndPlanning->sheet;
        $quantityMax = $this->quantityMaxGuesser->getMaxPlanning($sheet);

        if ($this->conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->hasConflict(
            $selectParticipantAndPlanning->sheet,
            $sheet->getPackage()->getPlanning(),
            $quantity
        )) {
            $this
                ->context
                ->buildViolation('package.product.quantityMinPromotionCode')
                ->atPath('planningQuantity.quantity')
                ->addViolation()
            ;
        }

        if ($quantity < 0 || $quantity > $quantityMax) {
            $this
                ->context
                ->buildViolation('package.planning.quantity')
                ->setParameters(
                    [
                        '%min%' => 0,
                        '%max%' => $quantityMax,
                    ]
                )
                ->atPath('planningQuantity.quantity')
                ->addViolation()
            ;
        }
    }
}
