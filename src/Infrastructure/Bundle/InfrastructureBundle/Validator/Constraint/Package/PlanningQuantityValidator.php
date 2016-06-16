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
        $quantity    = $selectParticipantAndPlanning->planningQuantity;
        $sheet       = $selectParticipantAndPlanning->sheet;
        $quantityMax = $this->quantityMaxGuesser->getMaxPlanning($sheet);

        if ($quantity < 0 || $quantity > $quantityMax) {
            $this
                ->context
                ->buildViolation('package.planning.quantity')
                ->setParameters(['%min%' => 0, '%max%' => $quantityMax])
                ->atPath('planningQuantity')
                ->addViolation();
        }
    }
}
