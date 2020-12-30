<?php

namespace Proximum\Vimeet\Application\Query\AvailabilityTimeRange;

use Proximum\Vimeet\Application\View\AvailabilityTimeRange\AvailabilityTimeRangeView;
use Proximum\Vimeet\Application\View\AvailabilityTimeRange\ProductView;
use Proximum\Vimeet\Domain\Model\Product;

class AvailabilityTimeRangeViewQueryHandler
{
    public function handle(AvailabilityTimeRangeViewQuery $query): AvailabilityTimeRangeView
    {
        return new AvailabilityTimeRangeView(
            $query->availabilityTimeRange->getName(),
            $query->availabilityTimeRange->getBegin(),
            $query->availabilityTimeRange->getEnd(),
            array_map(function (Product $product) {
                return new ProductView($product->getName());
            }, $query->availabilityTimeRange->getProducts())
        );
    }
}
