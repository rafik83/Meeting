<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Order\Export\CustomRowView;

class CustomRowViewQueryHandler
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param CustomRowViewQuery $query
     *
     * @return CustomRowView
     */
    public function handle(CustomRowViewQuery $query)
    {
        return new CustomRowView(
            $query->customRowIndex,
            $this->translator->trans('order.column.customRow.title', ['%customRowIndex%' => $query->customRowIndex], 'export', $query->adminLocale),
            $this->translator->trans('order.column.customRow.unitPrice', ['%customRowIndex%' => $query->customRowIndex], 'export', $query->adminLocale),
            $this->translator->trans('order.column.customRow.quantity', ['%customRowIndex%' => $query->customRowIndex], 'export', $query->adminLocale),
            $this->translator->trans('order.column.customRow.total', ['%customRowIndex%' => $query->customRowIndex], 'export', $query->adminLocale)
        );
    }
}
