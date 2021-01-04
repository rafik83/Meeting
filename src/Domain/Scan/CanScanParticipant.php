<?php


namespace Proximum\Vimeet\Domain\Scan;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

class CanScanParticipant
{
    /** @var Merger */
    private $merger;

    public function __construct(Merger $merger)
    {
        $this->merger = $merger;
    }

    public function isSatisfiedBy(Sheet $sheet): bool
    {
        $type = $sheet->getType();

        if ($type->canScanParticipant()) {
            return true;
        }

        $order = $this->merger->getMergedOrders($sheet);
        if (null === $order) {
            return false;
        }

        $options = $order->getOptions();
        foreach ($options as $option) {
            if ($option->canScanParticipant()) {
                return true;
            }
        }

        $plan = $order->getPlan();

        if (null === $plan) {
            return false;
        }

        $includedProducts = $plan->getIncludedOptionProduct();

        foreach ($includedProducts as $includedProduct) {
            if ($includedProduct->getIncluded()->canScanParticipant()) {
                return true;
            }
        }

        return false;
    }
}
