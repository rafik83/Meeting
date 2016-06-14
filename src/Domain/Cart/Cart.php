<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class Cart
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
     * @param Sheet $sheet
     */
    public function addSheetParticipantsToCart(Sheet $sheet)
    {
        $selectedPlan = $this->cartRowRepository->findCartRowPlanBySheet($sheet);

        if (null === $selectedPlan) {
            return;
        }

        $package = $sheet->getPackage();

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

        return max(0, $sheet->getParticipants()->count() - $includedParticipantNumber);
    }
}
