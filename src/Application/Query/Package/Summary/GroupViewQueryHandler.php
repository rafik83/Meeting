<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\GroupView;
use Proximum\Vimeet\Domain\Model\Product;

class GroupViewQueryHandler
{
    /**
     * @var ProductViewQueryHandler
     */
    private $productViewQueryHandler;

    /**
     * @param ProductViewQueryHandler $productViewQueryHandler
     */
    public function __construct(ProductViewQueryHandler $productViewQueryHandler)
    {
        $this->productViewQueryHandler = $productViewQueryHandler;
    }

    /**
     * @param GroupViewQuery $groupViewQuery
     *
     * @return GroupView
     */
    public function handle(GroupViewQuery $groupViewQuery)
    {
        $productViewQueryHandler = $this->productViewQueryHandler;

        $group   = $groupViewQuery->group;
        $options = array_map(function (Product $product) use ($productViewQueryHandler, $groupViewQuery) {
            if ($groupViewQuery->cart->hasProduct($product)) {
                return $productViewQueryHandler->handle(
                    new ProductViewQuery(
                        $groupViewQuery->sheet,
                        $product,
                        $groupViewQuery->cart,
                        $groupViewQuery->locale,
                        $groupViewQuery->planGroupView
                    )
                );
            } else {
                return null;
            }
        }, array_filter($group->getOptions(), function (Product $product) use ($groupViewQuery) {
            return null !== $groupViewQuery->cart->getCartRowForProduct($product);
        }));

        $total = 0;
        foreach ($options as $option) {
            if (null !== $option) {
                $total += $option->total;
            }
        }

        return new GroupView(
            $groupViewQuery->group->getLabel($groupViewQuery->locale),
            $options,
            $total
        );
    }
}
