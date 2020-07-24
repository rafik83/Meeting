<?php

namespace Proximum\Vimeet\Application\Components\Step;

use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Application\Components\Package\ProductByParticipantGetter;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

class StepParticipantAndPlanning
{
    /** @var Merger */
    private $orderMerger;

    /** @var CartManager */
    private $cartManager;

    /** @var ProductByParticipantGetter */
    private $productByParticipantGetter;

    public function __construct(
        Merger $orderMerger,
        CartManager $cartManager,
        ProductByParticipantGetter $productByParticipantGetter
    ) {
        $this->orderMerger = $orderMerger;
        $this->cartManager = $cartManager;
        $this->productByParticipantGetter = $productByParticipantGetter;
    }

    public function build(Sheet $sheet, ?int $stepIndex = null): SelectParticipantAndPlanning
    {
        $cart = $this->cartManager->getCart($sheet, $stepIndex);
        $command = new SelectParticipantAndPlanning($sheet, $stepIndex);
        $command->participantsProduct = $this->productByParticipantGetter->handle($cart);

        // Get Planning quantity
        $planningRow   = $cart->getPlanningRow();
        $orderQuantity = 0;
        $cartQuantity  = 0;

        $orderMerged = $this->orderMerger->getMergedOrders($sheet);

        if (null !== $orderMerged) {
            $planning = $sheet->getPackage()->getPlanning();

            if ($orderRow = $orderMerged->getRowForProduct($planning)) {
                $orderQuantity = $orderRow->getQuantity();
            }
        }

        if (null !== $planningRow) {
            $cartQuantity = $planningRow->getQuantity();
        }

        $command->planningQuantity = new OptionRow($orderQuantity + $cartQuantity);

        return $command;
    }
}
