<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Step;

use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

class StepParticipantAndPlanning
{
    /** @var Merger */
    private $orderMerger;

    /** @var CartManager */
    private $cartManager;

    /**
     * @param Merger      $orderMerger
     * @param CartManager $cartManager
     */
    public function __construct(Merger $orderMerger, CartManager $cartManager)
    {
        $this->orderMerger = $orderMerger;
        $this->cartManager = $cartManager;
    }

    /**
     * @param Sheet    $sheet
     * @param null|int $stepIndex
     *
     * @return SelectParticipantAndPlanning
     */
    public function build(Sheet $sheet, ?int $stepIndex = null): SelectParticipantAndPlanning
    {
        $cart = $this->cartManager->getCart($sheet, $stepIndex);
        $orderMerged = $this->orderMerger->getMergedOrders($sheet);

        $command = new SelectParticipantAndPlanning($sheet, $stepIndex);

        // Get product by participant from Cart
        foreach ($cart->getParticipantRows() as $cartRow) {
            foreach ($cartRow->getParticipants() as $participant) {
                $command->participantsProduct[$participant->getId()] = $cartRow->getProduct();
            }
        }

        // Set product to null or Product (from previous order) to others participants
        foreach ($sheet->getParticipantsArray() as $participant) {
            if (!isset($command->participantsProduct[$participant->getId()])) {
                $command->participantsProduct[$participant->getId()] = $participant->getParticipantProduct();
            }
        }

        // Set product to all participants if there only one participant product in the package
        $participantProducts = $sheet->getPackage()->getParticipants();

        if (1 === count($participantProducts)) {
            $participantProduct = reset($participantProducts);

            if (false !== $participantProduct) {
                foreach ($sheet->getParticipantsArray() as $participant) {
                    if (!isset($command->participantsProduct[$participant->getId()])) {
                        $command->participantsProduct[$participant->getId()] = $participantProduct;
                    }
                }
            }
        }

        // Get Planning quantity
        $planningRow   = $cart->getPlanningRow();
        $orderQuantity = 0;
        $cartQuantity  = 0;

        if (null !== $orderMerged) {
            $planning = $sheet->getPackage()->getPlanning();

            if ($orderRow = $orderMerged->getRowForProduct($planning)) {
                $orderQuantity = $orderRow->getQuantity();
            }
        }

        if (null !== $planningRow) {
            $cartQuantity = $planningRow->getQuantity();
        }

        $command->planningQuantity = $orderQuantity + $cartQuantity;

        return $command;
    }
}
