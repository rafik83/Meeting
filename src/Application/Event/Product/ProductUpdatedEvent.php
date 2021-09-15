<?php


namespace Proximum\Vimeet\Application\Event\Product;

use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Product;
use Symfony\Component\EventDispatcher\Event;

class ProductUpdatedEvent extends Event
{
    /** @var Product */
    public $product;

    /** @var AvailabilityTimeRange[] */
    public $previousAvailabilityTimeRanges;

    /**
     * ProductUpdatedEvent constructor.
     *
     * @param Product $product
     * @param AvailabilityTimeRange[] $previousAvailabilityTimeRanges
     */
    public function __construct(Product $product, array $previousAvailabilityTimeRanges)
    {
        $this->product = $product;
        $this->previousAvailabilityTimeRanges = $previousAvailabilityTimeRanges;
    }
}
