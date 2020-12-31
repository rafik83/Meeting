<?php

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\CustomRowView;

class CustomRowViewQueryHandler
{
    /**
     * @param CustomRowViewQuery $customRowViewQuery
     *
     * @return CustomRowView
     */
    public function handle(CustomRowViewQuery $customRowViewQuery)
    {
        $locale = $customRowViewQuery->locale;

        return new CustomRowView(
            $customRowViewQuery->row->getId(),
            $customRowViewQuery->row->getLabel($locale),
            $customRowViewQuery->row->getPrice(),
            $customRowViewQuery->row->getQuantity(),
            $customRowViewQuery->row->getOrder()->getCurrency(),
            $customRowViewQuery->row->getOrder()->getVatMode(),
            $customRowViewQuery->row->getVatRate()
        );
    }
}
