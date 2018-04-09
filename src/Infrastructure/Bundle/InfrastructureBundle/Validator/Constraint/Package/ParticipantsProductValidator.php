<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ParticipantsProductValidator extends ConstraintValidator
{
    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    public function __construct(AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository)
    {
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     * @param Constraint                   $constraint
     */
    public function validate($selectParticipantAndPlanning, Constraint $constraint)
    {
        $quantityIndexedByProductId = [];
        $event = $selectParticipantAndPlanning->sheet->getEvent();
        $availabilityTimeRanges = $this->availabilityTimeRangeRepository->findByEvent($event);

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

            $quantityIndexedByProductId[$product->getId()]++;

            if ($product->getQuantityMax() < $quantityIndexedByProductId[$product->getId()]) {
                $this
                    ->context
                    ->buildViolation('package.participantsProduct.quantityMaxReached')
                    ->atPath($participantId)
                    ->addViolation()
                ;
            }

            if (!empty($availabilityTimeRanges)) {
            }
        }
    }
}
