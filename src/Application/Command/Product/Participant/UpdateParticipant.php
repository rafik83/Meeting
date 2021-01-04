<?php

namespace Proximum\Vimeet\Application\Command\Product\Participant;

use Proximum\Vimeet\Application\Command\Product\AbstractUpdate;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Product;

class UpdateParticipant extends AbstractUpdate
{
    /** @var AvailabilityTimeRange[] */
    public $availabilityTimeRanges;

    public function __construct(Product $product)
    {
        parent::__construct($product);

        $this->availabilityTimeRanges = $product->getAvailabilityTimeRanges();
    }
}
