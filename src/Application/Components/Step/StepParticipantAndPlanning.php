<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
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
    /**
     * @var Merger
     */
    private $orderMerger;
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * StepParticipant constructor.
     *
     * @param Merger      $orderMerger
     * @param CartManager $cartManager
     */
    public function __construct(Merger $orderMerger, CartManager $cartManager)
    {
        $this->orderMerger = $orderMerger;
        $this->cartManager = $cartManager;
    }

    /**
     * @param Sheet $sheet
     * @param int   $stepIndex
     *
     * @return SelectParticipantAndPlanning
     */
    public function build(Sheet $sheet, $stepIndex)
    {
        $cart = $this->cartManager->getCart($sheet, $stepIndex);

        if ($sheet->hasNotCancelledOrders()) {
            $orderMerged = $this->orderMerger->merge($sheet->getNotCancelledOrders());
        }

        $command = new SelectParticipantAndPlanning($sheet, $stepIndex);

        // Get product by participant from Cart
        foreach ($cart->getParticipantRows() as $cartRow) {
            foreach ($cartRow->getParticipants() as $participant) {
                $command->participantsProduct[$participant->getId()] = $cartRow->getProduct();
            }
        }

        foreach ($command->sheet->getParticipantsArray() as $participant) {
            if (!isset($command->participantsProduct[$participant->getId()])) {
                $command->participantsProduct[$participant->getId()] = null;
            }
        }

        // Get Planning quantity
        $planningRow   = $cart->getPlanningRow();
        $orderQuantity = 0;
        $cartQuantity  = 0;

        if (isset($orderMerged)) {
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
