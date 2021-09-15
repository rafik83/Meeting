<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Application\View\Order\Export\CustomRowBoughtView;

class CustomRowBoughtViewQueryHandler
{
    /**
     * @param CustomRowBoughtViewQuery $query
     *
     * @return CustomRowBoughtView
     */
    public function handle(CustomRowBoughtViewQuery $query)
    {
        return new CustomRowBoughtView(
            $query->row->getId(),
            $query->row->getLabel(),
            $query->row->getPrice(),
            $query->row->getQuantity(),
            ($query->row->getQuantity() * $query->row->getPrice()) // total
        );
    }
}
