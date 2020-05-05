<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Order\Export\SharedColumnsTranslationView;

class SharedColumnsTranslationViewQueryHandler
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
     * @param SharedColumnsTranslationViewQuery $query
     *
     * @return SharedColumnsTranslationView
     */
    public function handle(SharedColumnsTranslationViewQuery $query)
    {
        return new SharedColumnsTranslationView(
            $this->translator->trans('order.column.order_id', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.order_date', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.sheet_id', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.sheet_title', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.invoice_number', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.invoice_date', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_gender', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_last_name', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_first_name', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_position', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_phone', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_mobile', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_email', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_company', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_street', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_zip_code', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_city', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_country', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_vat_number', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.billing_info_reference', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.order_total_without_vat', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.order_total_vat', [], 'export', $query->adminLocale),
            $this->translator->trans('order.column.order_total_with_vat', [], 'export', $query->adminLocale)
        );
    }
}
