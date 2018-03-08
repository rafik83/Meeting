<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package\ParticipantsProductValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ParticipantsProductValidatorTest extends TestCase
{
    public function testValidate()
    {
        $sheet = $this->prophesize(Sheet::class);

        $product = $this->prophesize(Product::class);
        $product->getId()->shouldBeCalled()->willReturn(999);
        $product->getQuantityMax()->shouldBeCalled()->willReturn(1);

        $selectParticipantAndPlanning = new SelectParticipantAndPlanning($sheet->reveal());
        $selectParticipantAndPlanning->participantsProduct = [
            1337 => $product->reveal(),
            1984 => $product->reveal(),
            404 => null,
        ];

        $executionContext = $this->prophesize(ExecutionContextInterface::class);
        $constraint = $this->prophesize(Constraint::class);

        $constraintViolationBuilder1 = $this->prophesize(ConstraintViolationBuilderInterface::class);
        $executionContext
            ->buildViolation('package.participantsProduct.quantityMaxReached')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder1->reveal())
        ;
        $constraintViolationBuilder1->atPath(1984)->shouldBeCalled()->willReturn($constraintViolationBuilder1->reveal());
        $constraintViolationBuilder1->addViolation()->shouldBeCalled();

        $constraintViolationBuilder2 = $this->prophesize(ConstraintViolationBuilderInterface::class);
        $executionContext
            ->buildViolation('package.participantsProduct.productMustBeSelected')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder2->reveal())
        ;
        $constraintViolationBuilder2->atPath(404)->shouldBeCalled()->willReturn($constraintViolationBuilder2->reveal());
        $constraintViolationBuilder2->addViolation()->shouldBeCalled();

        $participantsProductValidator = new ParticipantsProductValidator();
        $participantsProductValidator->initialize($executionContext->reveal());
        $participantsProductValidator->validate($selectParticipantAndPlanning, $constraint->reveal());
    }
}
