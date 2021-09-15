<?php

namespace Proximum\Vimeet\Application\Components\Step;

use Proximum\Vimeet\Application\Command\Package\Step\SelectPlan;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Sheet;

class StepPlan
{
    /** @var CartManager */
    private $cartManager;

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
    public function build(Sheet $sheet, $stepIndex): SelectPlan
    {
        $command = new SelectPlan($sheet, $stepIndex);
        $cart = $this->cartManager->getCart($command->sheet, $command->currentStep);
        $selectedPlan = $cart->getPlanRow();

        if (null !== $selectedPlan) {
            $command->plan = $selectedPlan->getProduct();
        }

        return $command;
    }
}
