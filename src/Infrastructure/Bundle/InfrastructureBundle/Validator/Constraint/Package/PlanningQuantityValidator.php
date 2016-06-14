<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\Package\Planning\QuantityMaxGuesser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PlanningQuantityValidator extends ConstraintValidator
{
    /**
     * @var QuantityMaxGuesser
     */
    private $quantityMaxGuesser;

    /**
     * @param QuantityMaxGuesser $quantityMaxGuesser
     */
    public function __construct(QuantityMaxGuesser $quantityMaxGuesser)
    {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     * @param Constraint                   $constraint
     */
    public function validate($selectParticipantAndPlanning, Constraint $constraint)
    {
        $quantity = $selectParticipantAndPlanning->planningQuantity;
        $sheet    = $selectParticipantAndPlanning->sheet;

        if ($quantity < 0 || $quantity > $this->quantityMaxGuesser->getMaxPlanning($sheet)) {
            $this
                ->context
                ->buildViolation('package.planning.quantity')
                ->setParameters(['%min%' => 0, '%max%' => 1])
                ->addViolation();
        }
    }
}
