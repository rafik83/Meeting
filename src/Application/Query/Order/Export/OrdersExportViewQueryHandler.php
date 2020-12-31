<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Application\View\Order\Export\OrdersExportView;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class OrdersExportViewQueryHandler
{
    /** @var PromotionCodeViewQueryHandler */
    private $promotionCodeViewQueryHandler;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    /** @var ProductViewQueryHandler */
    private $productViewQueryHandler;

    /** @var OrderViewQueryHandler */
    private $orderViewQueryHandler;

    /** @var CustomRowViewQueryHandler */
    private $customRowViewQueryHandler;

    /** @var SharedColumnsTranslationViewQueryHandler */
    private $sharedColumnsTranslationViewQueryHandler;

    /**
     * @param OrderRepositoryInterface                 $orderRepository
     * @param ProductRepositoryInterface               $productRepository
     * @param PromotionCodeRepositoryInterface         $promotionCodeRepository
     * @param ProductViewQueryHandler                  $productViewQueryHandler
     * @param PromotionCodeViewQueryHandler            $promotionCodeViewQueryHandler
     * @param OrderViewQueryHandler                    $orderViewQueryHandler
     * @param CustomRowViewQueryHandler                $customRowViewQueryHandler
     * @param SharedColumnsTranslationViewQueryHandler $sharedColumnsTranslationViewQueryHandler
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        ProductViewQueryHandler $productViewQueryHandler,
        PromotionCodeViewQueryHandler $promotionCodeViewQueryHandler,
        OrderViewQueryHandler $orderViewQueryHandler,
        CustomRowViewQueryHandler $customRowViewQueryHandler,
        SharedColumnsTranslationViewQueryHandler $sharedColumnsTranslationViewQueryHandler
    ) {
        $this->orderRepository                          = $orderRepository;
        $this->productRepository                        = $productRepository;
        $this->promotionCodeRepository                  = $promotionCodeRepository;
        $this->productViewQueryHandler                  = $productViewQueryHandler;
        $this->promotionCodeViewQueryHandler            = $promotionCodeViewQueryHandler;
        $this->orderViewQueryHandler                    = $orderViewQueryHandler;
        $this->customRowViewQueryHandler                = $customRowViewQueryHandler;
        $this->sharedColumnsTranslationViewQueryHandler = $sharedColumnsTranslationViewQueryHandler;
    }

    /**
     * @param OrdersExportViewQuery $query
     *
     * @return OrdersExportView
     */
    public function handle(OrdersExportViewQuery $query): OrdersExportView
    {
        $locale = $query->event->getFallback();
        $boughtProducts = $this->productRepository->findProductsBoughtByEvent($query->event);
        $promotionCodes = $this->promotionCodeRepository->findBoughtByEvent($query->event);

        // Initiate the empty array for the products views, promotion code views, order views and customRow views
        $plans              = [];
        $participants       = [];
        $plannings          = [];
        $options            = [];
        $promotionCodesView = [];
        $orderViews         = [];
        $customRowViews     = [];

        foreach ($boughtProducts as $product) {
            $productView = $this->productViewQueryHandler->handle(
                new ProductViewQuery($product, $locale, $query->adminLocale)
            );

            if ($product->isPlan()) {
                $plans[] = $productView;
            } elseif ($product->isParticipant()) {
                $participants[] = $productView;
            } elseif ($product->isPlanning()) {
                $plannings[] = $productView;
            } elseif ($product->isOption()) {
                $options[] = $productView;
            }
        }

        foreach ($promotionCodes as $promotionCode) {
            $promotionCodesView[] = $this->promotionCodeViewQueryHandler->handle(
                new PromotionCodeViewQuery($promotionCode, $query->adminLocale)
            );
        }

        $maxIndexOfCustomRow = 0;
        $orders = $this->orderRepository->findNotCancelledWithJoinRowAndPromotionCodeByEvent($query->event);
        $this->orderViewQueryHandler->preloadBillingInfo($query->event);

        foreach ($orders as $order) {
            $orderView = $this->orderViewQueryHandler->handle(
                new OrderViewQuery($query->event, $order, $locale, $query->adminLocale)
            );

            if ($orderView->countCustomRows() > $maxIndexOfCustomRow) {
                $maxIndexOfCustomRow = $orderView->countCustomRows();
            }

            $orderViews[] = $orderView;
        }

        for ($index = 1; $index <= $maxIndexOfCustomRow; ++$index) {
            $customRowViews[] = $this->customRowViewQueryHandler->handle(new CustomRowViewQuery($index, $query->adminLocale));
        }

        return new OrdersExportView(
            $this->sharedColumnsTranslationViewQueryHandler->handle(new SharedColumnsTranslationViewQuery($query->adminLocale)),
            array_merge($plans, $participants, $plannings, $options),
            $promotionCodesView,
            $orderViews,
            $customRowViews
        );
    }
}
