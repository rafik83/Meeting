<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Cart\Cart;
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
     * @var Cart
     */
    private $cart;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @param CartRowRepositoryInterface $cartRowRepository
     * @param Cart                       $cart
     * @param \DateTimeInterface         $datetime
     */
    public function __construct(CartRowRepositoryInterface $cartRowRepository, Cart $cart, \DateTimeInterface $datetime)
    {
        $this->cartRowRepository = $cartRowRepository;
        $this->cart              = $cart;
        $this->datetime          = $datetime;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     */
    public function handle(SelectParticipantAndPlanning $selectParticipantAndPlanning)
    {
        $sheet   = $selectParticipantAndPlanning->sheet;
        $package = $sheet->getPackage();

        // Add participants number to cart
        $this->cart->addSheetParticipantsToCart($sheet);

        // Find a Planning CartRow
        $planningCartRow = $this->cartRowRepository->findCartRowPlanningBySheet($sheet);

        if (null === $planningCartRow
            || $planningCartRow->getQuantity() !== $selectParticipantAndPlanning->planningQuantity
        ) {
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
}
