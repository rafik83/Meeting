<?php

namespace Proximum\Vimeet\Application\View\Product;

class ProductsListView
{
    /** @var ProductsListView[] */
    public $products;

    /** @var string */
    public $locale;

    public function __construct(
        array $products,
        string $locale
    ) {
        $this->products = $products;
        $this->locale = $locale;
    }
}
