<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class SelectParticipantAndPlanningHandler
{
    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @param CartRowRepositoryInterface $cartRowRepository
     * @param \DateTimeInterface         $datetime
     */
    public function __construct(CartRowRepositoryInterface $cartRowRepository, \DateTimeInterface $datetime)
    {
        $this->cartRowRepository = $cartRowRepository;
        $this->datetime          = $datetime;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     */
    public function handle(SelectParticipantAndPlanning $selectParticipantAndPlanning)
    {
        $sheet        = $selectParticipantAndPlanning->sheet;
        $selectedPlan = $this->cartRowRepository->findCartRowPlanBySheet($sheet);
        $package      = $sheet->getPackage();

        // Find a Participant CartRow
        $participantCartRow = $this->cartRowRepository->findCartRowParticipantBySheet($sheet);
        
        if (null !== $participantCartRow) {
            // Delete previous participant CartRow
            $this->cartRowRepository->delete($participantCartRow);
        }

        $additionalParticipantsNumber = $this->getAdditionalParticipantsNumber($selectedPlan->getProduct(), $sheet);

        if ($additionalParticipantsNumber > 0) {
            // Add participant product and quantity
            $this->cartRowRepository->add(
                new CartRow(
                    $sheet,
                    $package->getParticipant(),
                    $additionalParticipantsNumber,
                    $this->datetime
                )
            );
        }

        // Find a Planning CartRow
        $planningCartRow = $this->cartRowRepository->findCartRowPlanningBySheet($sheet);

        if (null === $planningCartRow || $planningCartRow->getQuantity() !== $selectParticipantAndPlanning->planningQuantity) {
            if (null !== $planningCartRow) {
                // Delete previous planning CartRow
                $this->cartRowRepository->delete($planningCartRow);
            }

            if (null !== $package->getPlanning() && $selectParticipantAndPlanning->planningQuantity > 0) {
                // Add planning product and quantity
                $this->cartRowRepository->add(
                    new CartRow(
                        $sheet,
                        $package->getPlanning(),
                        $selectParticipantAndPlanning->planningQuantity,
                        $this->datetime
                    )
                );
            }
        }
    }

    /**
     * @param Product $plan
     * @param Sheet   $sheet
     *
     * @return int
     */
    private function getAdditionalParticipantsNumber(Product $plan, Sheet $sheet)
    {
        $includedParticipantNumber  = 0;
        $includedParticipantProduct = $plan->getIncludedParticipantProduct();

        if ($includedParticipantProduct) {
            $includedParticipantNumber = $includedParticipantProduct->getQuantity();
        }

        return $sheet->getParticipants()->count() - $includedParticipantNumber;
    }
}
