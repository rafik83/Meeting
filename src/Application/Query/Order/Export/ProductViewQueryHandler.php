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
use Proximum\Vimeet\Application\View\Order\Export\ProductView;

class ProductViewQueryHandler
{
    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param ProductViewQuery $query
     *
     * @return ProductView
     */
    public function handle(ProductViewQuery $query)
    {
        $productTitle = $query->product->getTitle($query->locale);

        return new ProductView(
            $query->product->getId(),
            $productTitle,
            $this->translator->trans('order.column.product.unitPrice', ['%productTitle%' => $productTitle], 'export', $query->adminLocale),
            $this->translator->trans('order.column.product.quantity', ['%productTitle%' => $productTitle], 'export', $query->adminLocale),
            $this->translator->trans('order.column.product.total', ['%productTitle%' => $productTitle], 'export', $query->adminLocale)
        );
    }
}
