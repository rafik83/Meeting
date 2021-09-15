<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\AvailabilityTimeRange\Product\ParticipantProductWithAvailabilityTimeRangeChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package\ParticipantsProductValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ParticipantsProductValidatorTest extends TestCase
{
    /** @var ObjectProphecy */
    private $availabilityTimeRangeRepository;

    /** @var ObjectProphecy */
    private $participantProductWithAvailabilityTimeRangeChecker;

    public function setUp()
    {
        $this->availabilityTimeRangeRepository = $this->prophesize(AvailabilityTimeRangeRepositoryInterface::class);
        $this->participantProductWithAvailabilityTimeRangeChecker = $this->prophesize(ParticipantProductWithAvailabilityTimeRangeChecker::class);
    }

    public function testValidate(): void
    {
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);
        $participant4 = $this->prophesize(Participant::class);
        $participant5 = $this->prophesize(Participant::class);

        $participant1->getId()->willReturn(1337);
        $participant2->getId()->willReturn(1984);
        $participant3->getId()->willReturn(1818);
        $participant4->getId()->willReturn(1919);
        $participant5->getId()->willReturn(404);
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getParticipantsArray()->willReturn([
            $participant1->reveal(),
            $participant2->reveal(),
            $participant3->reveal(),
            $participant4->reveal(),
            $participant5->reveal(),
        ]);

        $product = $this->prophesize(Product::class);
        $product->getId()->shouldBeCalled()->willReturn(999);
        $product->getQuantityMax()->shouldBeCalled()->willReturn(1);
        $product2 = $this->prophesize(Product::class);
        $product2->getId()->shouldBeCalled()->willReturn(1001);
        $product2->getQuantityMax()->shouldBeCalled()->willReturn(3);

        $selectParticipantAndPlanning = new SelectParticipantAndPlanning($sheet->reveal());
        $selectParticipantAndPlanning->participantsProduct = [
            1337 => $product->reveal(),
            1984 => $product->reveal(),
            1818 => $product2->reveal(),
            1919 => $product2->reveal(),
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

        $this->availabilityTimeRangeRepository->hasByEvent($event->reveal())->shouldBeCalled()->willReturn(true);

        $this->participantProductWithAvailabilityTimeRangeChecker
            ->canSetProduct($participant3->reveal(), $product2->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->participantProductWithAvailabilityTimeRangeChecker
            ->canSetProduct($participant4->reveal(), $product2->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $this->participantProductWithAvailabilityTimeRangeChecker
            ->canSetProduct($participant1->reveal(), $product->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $constraintViolationBuilder3 = $this->prophesize(ConstraintViolationBuilderInterface::class);
        $executionContext
            ->buildViolation('package.participantsProduct.participantHasMeetingOrHappeningOnPreviousAvailabilityTimeRange')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder3->reveal())
        ;
        $constraintViolationBuilder3->atPath(1919)->shouldBeCalled()->willReturn($constraintViolationBuilder3->reveal());
        $constraintViolationBuilder3->addViolation()->shouldBeCalled();

        $participantsProductValidator = new ParticipantsProductValidator(
            $this->availabilityTimeRangeRepository->reveal(),
            $this->participantProductWithAvailabilityTimeRangeChecker->reveal()
        );
        $participantsProductValidator->initialize($executionContext->reveal());
        $participantsProductValidator->validate($selectParticipantAndPlanning, $constraint->reveal());
    }
}
