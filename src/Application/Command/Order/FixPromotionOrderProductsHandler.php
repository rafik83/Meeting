<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Adapter\EntityManagerAdapterInterface;
use Proximum\Vimeet\Application\Components\Order\FixProductPromotionCodes\ProductPromotionCodesFixStrategyGuesser;
use Proximum\Vimeet\Application\Components\Order\OrderHelper;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class FixPromotionOrderProductsHandler
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var EntityManagerAdapterInterface */
    private $entityManagerAdapter;
    
    /**@var OrderHelper */
    private $orderHelper;
    
    /**@var ProductPromotionCodesFixStrategyGuesser */
    private $fixStrategyGuesser;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        EntityManagerAdapterInterface $entityManagerAdapter,
        OrderHelper $orderHelper,
        ProductPromotionCodesFixStrategyGuesser $fixStrategyGuesser
    ) {
        $this->orderRepository = $orderRepository;
        $this->entityManagerAdapter = $entityManagerAdapter;
        $this->orderHelper = $orderHelper;
        $this->fixStrategyGuesser = $fixStrategyGuesser;
    }

    public function handle(FixPromotionOrderProducts $fixPromotionOrderProducts): void
    {
        $orders = $this->orderRepository->findWithPromotion();
        $ordersCount = 0;
        $modifiedOrdersCount = 0;
        
        foreach ($orders as $order) {
            $ordersCount++;
            try {
                $this->fixOrder($order);
                $modifiedOrdersCount++;
                $check = $this->orderHelper->checkPromotionCodes($order);

                if (false === $check) {
                    throw new \UnexpectedValueException($order->getId());
                }
            } catch (\UnexpectedValueException $exception) {
                $this->displayOrderDetail($order);
            }
        }

        if (false === $fixPromotionOrderProducts->dry) {
            // no need to persist new promotion codes since there's a cascade persist from order entity
            $this->entityManagerAdapter->flush();
        }

        echo sprintf(
            "%F%%\n%d not fixed orders\n%d fixed orders\n%d total orders\n",
            round($modifiedOrdersCount * 100 / $ordersCount, 2),
            $ordersCount - $modifiedOrdersCount,
            $modifiedOrdersCount,
            $ordersCount
        );
    }

    private function fixOrder(Order $order): void
    {
        $guesser = $this->fixStrategyGuesser->create();
        $strategy = $guesser->guess($order);

        // if strategy found
        if (!$strategy) {
            throw new \UnexpectedValueException('No strategy');
        }

        $strategy->fix($order);
    }

    /**
     * @param Order $order
     * @param array $existingPromotionCodeRows
     * @param array $newPromotionCodeRows
     */
    private function displayPromotionCodeDetail(
        Order $order,
        array $existingPromotionCodeRows,
        array $newPromotionCodeRows
    ): void {
        $existingPrice = 0;
        $existingCountElts = 0;
        
        foreach ($existingPromotionCodeRows as $existingPromotionCodeRow) {
            $existingPrice += $existingPromotionCodeRow->getPrice();
            $existingCountElts++;
        }

        $newPrice = 0;
        $newCountElts = 0;
        
        foreach ($newPromotionCodeRows as $existingPromotionCodeRow) {
            $newPrice += $existingPromotionCodeRow->getPrice();
            $newCountElts++;
        }

        $countResult = 'KO';
        if ($existingCountElts === $newCountElts) {
            $countResult = '===';
        }
        if ($order->getId() < 5205 && $existingCountElts === 1 && $newCountElts > 1) {
            $countResult = '0<>';
        }
        $discountDiff = round(
            $existingPrice - $newPrice,
            2
        );

        $countRows = count($order->getRows());

        echo 'tofix: '
            .str_pad($order->getSheet()->getEvent()->getId(), 3, ' ', STR_PAD_LEFT).'-'
            .str_pad($order->getSheet()->getId(), 6, ' ', STR_PAD_LEFT).'-'
            .str_pad($order->getId(), 5, ' ', STR_PAD_LEFT)
            .' diff: '
            .str_pad($discountDiff, 6, ' ', STR_PAD_LEFT)
            .' '.$existingPrice.' '.$newPrice
            .' count: '.$countResult.' '.$countRows.'/'.$existingCountElts.'/'.$newCountElts."\n";
    }

    /**
     * @param Order $order
     */
    private function displayOrderDetail(Order $order): void
    {
        $modelPromotionCodes = $this->orderHelper->getModelPromotionCodes($order);

        // display some data since there's no strategy
        foreach ($modelPromotionCodes as $modelPromotionCode) {
            $newPromotionCodeRows = $this->orderHelper->convertToPromotionCodes($order, $modelPromotionCode);
            $existingPromotionCodeRows = $this->orderHelper->getPromotionCodes($order, $modelPromotionCode);
            $this->displayPromotionCodeDetail($order, $existingPromotionCodeRows, $newPromotionCodeRows);
        }
    }
}
