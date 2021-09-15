<?php

namespace Proximum\Vimeet\Domain\Sheet\Product;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SelectedProductAssigner
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var Merger */
    private $merger;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        Merger $merger,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->merger = $merger;
        $this->sheetRepository = $sheetRepository;
    }

    public function handle(Sheet $sheet): void
    {
        $orders = $sheet->getNotCancelledOrders();

        if (empty($orders)) {
            return;
        }

        $orderMerged = $this->merger->merge($orders);
        $orderMergedProducts = $this->getOrderMergedProducts($orderMerged);

        $templateData = $this->templateDataFactory->createFromSheet($sheet, null);

        foreach ($templateData->getObjects() as $object) {
            if (!$object->isBuyable()) {
                continue;
            }

            $selectedProduct = $object->getSelectedProduct();

            if (null !== $selectedProduct && isset($orderMergedProducts[$selectedProduct])) {
                continue;
            }

            $productsBought = array_intersect($orderMergedProducts, $object->getProducts());

            if (empty($productsBought)) {
                $object->setSelectedProduct(null);

                continue;
            }

            $object->setSelectedProductId(reset($productsBought));
        }

        $sheet->setData($templateData->getData());

        $this->sheetRepository->set($sheet);
    }

    /**
     * @param Order $orderMerged
     *
     * @return array
     */
    private function getOrderMergedProducts(Order $orderMerged): array
    {
        $orderMergedProducts = [];

        foreach ($orderMerged->getRows() as $row) {
            if (Product::TYPE_OPTION !== $row->getType()) {
                continue;
            }

            if (null === $row->getProduct()) {
                continue;
            }

            $orderMergedProducts[$row->getProduct()->getId()] = $row->getProduct()->getId();
        }

        return $orderMergedProducts;
    }
}
