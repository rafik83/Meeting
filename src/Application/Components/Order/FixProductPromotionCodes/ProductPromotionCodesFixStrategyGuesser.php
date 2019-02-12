<?php

namespace Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes;

use Proximum\Vimeet\Domain\Model\Order;

class ProductPromotionCodesFixStrategyGuesser
{
    /** @var FixProductPromotionStrategyInterface[] */
    private $strategies = [];

    public static function create(): ProductPromotionCodesFixStrategyGuesser
    {
        $fixer = new self;

        $fixer->addStrategy(new SinglePromoSingleRowStrategy());

        $fixer->addStrategy(new SinglePromoOneMatchingProductStrategy());

        $fixer->addStrategy(new SingleProductPromotionStrategy());

        $fixer->addStrategy(new FullCompareStrategy());

        $fixer->addStrategy(new SplitStrategy());

        return $fixer;
    }

    /**
     * @param FixProductPromotionStrategyInterface $fixProductPromotionStrategy
     */
    public function addStrategy(FixProductPromotionStrategyInterface $fixProductPromotionStrategy): void
    {
        $this->strategies[] = $fixProductPromotionStrategy;
    }

    /**
     * @param Order $order
     *
     * @return FixProductPromotionStrategyInterface|null
     */
    public function guess(Order $order): ?FixProductPromotionStrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->canApply($order)) {
                return $strategy;
            }
        }

        return null;
    }

    private function __construct()
    {
    }
}
