<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Product\ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode;
use Proximum\Vimeet\Domain\Package\Product\QuantityMaxGuesser;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package\PlanningQuantityValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class PlanningQuantityValidatorTest extends TestCase
{
    public function testValidate()
    {
        $planningProduct = $this->prophesize(Product::class);

        $package = $this->prophesize(Package::class);
        $package->getPlanning()->shouldBeCalled()->willReturn($planningProduct->reveal());

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getPackage()->shouldBeCalled()->willReturn($package->reveal());

        $quantityMaxGuesser = $this->prophesize(QuantityMaxGuesser::class);
        $quantityMaxGuesser->getMaxPlanning($sheet->reveal())->shouldBeCalled()->willReturn(1);

        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode = $this->prophesize(
            ConflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode::class
        );
        $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode
            ->hasConflict($sheet->reveal(), $planningProduct->reveal(), 2)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $constraint = $this->prophesize(Constraint::class);
        $executionContext = $this->prophesize(ExecutionContextInterface::class);

        $constraintViolationBuilderQuantityMinPromotionCode = $this->prophesize(ConstraintViolationBuilderInterface::class);
        $executionContext
            ->buildViolation('package.product.quantityMinPromotionCode')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilderQuantityMinPromotionCode->reveal());
        $constraintViolationBuilderQuantityMinPromotionCode
            ->atPath('planningQuantity.quantity')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilderQuantityMinPromotionCode->reveal());
        $constraintViolationBuilderQuantityMinPromotionCode
            ->addViolation()
            ->shouldBeCalled();

        $constraintViolationBuilderPlanningQuantity = $this->prophesize(ConstraintViolationBuilderInterface::class);
        $executionContext
            ->buildViolation('package.planning.quantity')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilderPlanningQuantity->reveal());
        $constraintViolationBuilderPlanningQuantity
            ->setParameters(
                [
                    '%min%' => 0,
                    '%max%' => 1,
                ]
            )
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilderPlanningQuantity->reveal())
        ;
        $constraintViolationBuilderPlanningQuantity
            ->atPath('planningQuantity.quantity')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilderPlanningQuantity->reveal());
        $constraintViolationBuilderPlanningQuantity
            ->addViolation()
            ->shouldBeCalled();

        $selectParticipantAndPlanning = new SelectParticipantAndPlanning($sheet->reveal());
        $selectParticipantAndPlanning->planningQuantity = new OptionRow(2, []);

        $planningQuantityValidator = new PlanningQuantityValidator(
            $quantityMaxGuesser->reveal(),
            $conflictBetweenChoosenQuantityAndPreviouslyUsedPromotionCode->reveal()
        );
        $planningQuantityValidator->initialize($executionContext->reveal());
        $planningQuantityValidator->validate(
            $selectParticipantAndPlanning,
            $constraint->reveal()
        );
    }
}
