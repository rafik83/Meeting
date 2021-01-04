<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\AvailabilityTimeRange\Product\ParticipantProductWithAvailabilityTimeRangeChecker;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ParticipantsProductValidator extends ConstraintValidator
{
    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    /** @var ParticipantProductWithAvailabilityTimeRangeChecker */
    private $participantProductWithAvailabilityTimeRangeChecker;

    public function __construct(
        AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository,
        ParticipantProductWithAvailabilityTimeRangeChecker $participantProductWithAvailabilityTimeRangeChecker
    ) {
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
        $this->participantProductWithAvailabilityTimeRangeChecker = $participantProductWithAvailabilityTimeRangeChecker;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     * @param Constraint                   $constraint
     */
    public function validate($selectParticipantAndPlanning, Constraint $constraint)
    {
        $quantityIndexedByProductId = [];
        $event = $selectParticipantAndPlanning->sheet->getEvent();
        $hasAvailabilityTimeRanges = $this->availabilityTimeRangeRepository->hasByEvent($event);

        $participants = $selectParticipantAndPlanning->sheet->getParticipantsArray();
        $participantsById = [];

        foreach ($participants as $participant) {
            $participantsById[$participant->getId()] = $participant;
        }

        foreach ($selectParticipantAndPlanning->participantsProduct as $participantId => $product) {
            if (!$product instanceof Product) {
                $this
                    ->context
                    ->buildViolation('package.participantsProduct.productMustBeSelected')
                    ->atPath($participantId)
                    ->addViolation()
                ;

                continue;
            }

            if (!isset($quantityIndexedByProductId[$product->getId()])) {
                $quantityIndexedByProductId[$product->getId()] = 0;
            }

            ++$quantityIndexedByProductId[$product->getId()];

            if ($product->getQuantityMax() < $quantityIndexedByProductId[$product->getId()]) {
                $this
                    ->context
                    ->buildViolation('package.participantsProduct.quantityMaxReached')
                    ->atPath($participantId)
                    ->addViolation()
                ;

                continue;
            }

            if (true === $hasAvailabilityTimeRanges && isset($participantsById[$participantId])) {
                $canSetProduct = $this->participantProductWithAvailabilityTimeRangeChecker
                    ->canSetProduct(
                        $participantsById[$participantId],
                        $product
                    )
                ;

                if (false === $canSetProduct) {
                    $this
                        ->context
                        ->buildViolation('package.participantsProduct.participantHasMeetingOrHappeningOnPreviousAvailabilityTimeRange')
                        ->atPath($participantId)
                        ->addViolation()
                    ;
                }
            }
        }
    }
}
