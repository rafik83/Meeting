<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Step;

use Proximum\Vimeet\Application\Command\Package\Step\SelectPlan;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Sheet;

class StepPlan
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * StepPlan constructor.
     *
     * @param CartManager $cartManager
     */
    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

    /**
     * @param Sheet $sheet
     * @param int   $stepIndex
     *
     * @return SelectPlan
     */
    public function build(Sheet $sheet, $stepIndex)
    {
        $command      = new SelectPlan($sheet, $stepIndex);
        $cart         = $this->cartManager->getCart($command->sheet, $command->currentStep);
        $selectedPlan = $cart->getPlanRow();

        if (null !== $selectedPlan) {
            $command->plan = $selectedPlan->getProduct();
        }

        return $command;
    }
}
