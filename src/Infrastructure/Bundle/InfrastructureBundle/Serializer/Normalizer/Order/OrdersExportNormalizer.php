<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Order;

use Proximum\Vimeet\Application\View\Order\Export\OrdersExportView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrdersExportNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof OrdersExportView) {
            throw new \Exception('Invalid object');
        }

        $columns = $this->createColumnName($object);
        $data[]  = $columns;

        foreach ($object->orders as $order) {
            $order->setColumnArray($columns);
            $data[] = $this->normalizer->normalize($order, $format, $context);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof OrdersExportView && $format === 'csv';
    }

    /**
     * @param OrdersExportView $object
     *
     * @return array
     */
    private function createColumnName(OrdersExportView $object)
    {
        $data = [];

        // Shared columns
        foreach ($object->sharedColumnsTranslationView->getAllColumns() as $columnKey => $columnTranslation) {
            $data[$columnKey] = $columnTranslation;
        }

        // product column
        foreach ($object->products as $product) {
            $data[$product->getUnitPriceColumnId()] = $product->productTitleWithUnitPriceTranslation;
            $data[$product->getQuantityColumnId()]  = $product->productTitleWithQuantityTranslation;
            $data[$product->getTotalColumnId()]     = $product->productTitleWithTotalTranslation;
        }

        foreach ($object->promotionCodes as $promotionCode) {
            $data[$promotionCode->getQuantityColumnId()] = $promotionCode->promotionCodeTitleWithQuantityTranslation;
            $data[$promotionCode->getTotalColumnId()]    = $promotionCode->promotionCodeTitleWithTotalTranslation;
        }

        foreach ($object->customRowsColumns as $customRow) {
            $data[$customRow->getTitleColumnId()]     = $customRow->title;
            $data[$customRow->getUnitPriceColumnId()] = $customRow->unitPriceTitle;
            $data[$customRow->getQuantityColumnId()]  = $customRow->quantityTitle;
            $data[$customRow->getTotalColumnId()]     = $customRow->totalTitle;
        }

        return $data;
    }
}
