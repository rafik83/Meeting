<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Application\View\Order\Export\ProductBoughtView;

class ProductBoughtViewQueryHandler
{
    /**
     * @param ProductBoughtViewQuery $query
     *
     * @return ProductBoughtView
     */
    public function handle(ProductBoughtViewQuery $query)
    {
        return new ProductBoughtView(
            $query->row->getProductId(),
            $query->row->getPrice(),
            $query->row->getQuantity(),
            ($query->row->getQuantity() * $query->row->getPrice()) // total
        );
    }
}
