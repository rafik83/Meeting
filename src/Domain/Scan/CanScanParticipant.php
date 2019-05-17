<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
        $options = [];

        if (!$type->canScanParticipant()) {
            $order = $this->merger->getMergedOrders($sheet);
            if (null !== $order) {
                $options = $order->getOptions();
            }

            $hasScanOption = false;
            foreach ($options as $option) {
                if($option->canScanParticipant()) {
                    $hasScanOption = true;
                }
            }

            return $hasScanOption;
        }

        return $type->canScanParticipant();
    }
}
