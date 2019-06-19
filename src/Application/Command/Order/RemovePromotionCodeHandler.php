<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Domain\Repository\Order\PromotionCodeRepositoryInterface;

class RemovePromotionCodeHandler
{
    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    public function __construct(
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->promotionCodeRepository = $promotionCodeRepository;
        $this->sheetIndexer = $sheetIndexer;
    }

    public function handle(RemovePromotionCode $removePromotionCode): void
    {
        $promotionCode = $removePromotionCode->promotionCode;
        $order = $removePromotionCode->order;

        if (!in_array($promotionCode, $order->getPromotionCodes(), true)) {
            throw new \InvalidArgumentException('Given promotionCode is not in this order');
        }

        $order->removePromotionCode($promotionCode);
        $this->promotionCodeRepository->remove($promotionCode);
        $this->sheetIndexer->updateSheets([$order->getSheet()]);
    }
}
