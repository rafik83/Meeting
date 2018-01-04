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
        $command = new SelectParticipantAndPlanning($sheet, $stepIndex);
        $cart    = $this->cartManager->getCart($command->sheet, $command->currentStep);

        foreach ($command->sheet->getParticipantsArray() as $participant) {
            $command->participantsProduct[$participant->getId()] = null;
        }

        if ($command->sheet->hasNotCancelledOrders()) {
            $orderMerged = $this->orderMerger->merge($command->sheet->getNotCancelledOrders());
        }

        $planningRow   = $cart->getPlanningRow();
        $orderQuantity = 0;
        $cartQuantity  = 0;

        if (isset($orderMerged)) {
            $planning = $command->sheet->getPackage()->getPlanning();

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
