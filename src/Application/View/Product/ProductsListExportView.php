<?php

namespace Proximum\Vimeet\Application\View\Product\Export;

use Proximum\Vimeet\Application\View\Product\ProductsListView;

class ProductsListExportView
{
    /** @var ProductsListView[] */
    public $products;

    /** @var string */
    public $locale;

    public function __construct(
        ProductsListView $listView,
        string $locale
    ) {
        $this->products = $listView;
        $this->locale = $locale;
    }
}
