<?php

namespace Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes;

use Proximum\Vimeet\Application\Components\Order\OrderHelper;
use Proximum\Vimeet\Domain\Model\Order;

class ProductPromotionCodesFixStrategyGuesser
{
    /** @var FixProductPromotionStrategyInterface[] */
    private $strategies = [];
    
    /** @var OrderHelper */
    private $orderHelper;
    
    public function __construct(OrderHelper $orderHelper)
    {
        $this->orderHelper = $orderHelper;
    }

    public function create(): ProductPromotionCodesFixStrategyGuesser
    {
        $this->addStrategy(new SinglePromoSingleRowStrategy());
        $this->addStrategy(new SinglePromoOneMatchingProductStrategy());
        $this->addStrategy(new SingleProductPromotionStrategy());
        $this->addStrategy(new FullCompareStrategy($this->orderHelper));
        $this->addStrategy(new SplitStrategy($this->orderHelper));
        
        return $this;
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
}
