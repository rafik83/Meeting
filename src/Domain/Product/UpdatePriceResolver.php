<?php

namespace Proximum\Vimeet\Domain\Product;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;

class UpdatePriceResolver
{
    /** @var CartRowRepositoryInterface */
    private $cartRowRepository;

    /** @var RowRepositoryInterface */
    private $orderRowRepository;

    /**
     * @param CartRowRepositoryInterface $cartRowRepository
     * @param RowRepositoryInterface     $orderRowRepository
     */
    public function __construct(
        CartRowRepositoryInterface $cartRowRepository,
        RowRepositoryInterface $orderRowRepository
    ) {
        $this->cartRowRepository = $cartRowRepository;
        $this->orderRowRepository = $orderRowRepository;
    }

    /**
     * Check if product price can be updated
     *
     * @param Product $product
     *
     * @return bool
     */
    public function resolve(Product $product)
    {
        $cartRows  = $this->cartRowRepository->findByProduct($product);
        $orderRows = $this->orderRowRepository->findByProduct($product);

        return 0 === \count($cartRows) && 0 === \count($orderRows);
    }
}
