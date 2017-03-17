<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Order;

use Proximum\Vimeet\Application\View\Order\Export\OrderView;
use Proximum\Vimeet\Application\View\Order\Export\SharedColumnsTranslationView;
use Proximum\Vimeet\Domain\Model\Order;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrderRowNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof OrderView) {
            throw new \Exception('Invalid object');
        }

        $data = [
            SharedColumnsTranslationView::COLUMN_ORDER_ID                => $object->orderId,
            SharedColumnsTranslationView::COLUMN_SHEET_ID                => $object->sheetId,
            SharedColumnsTranslationView::COLUMN_SHEET_TITLE             => $object->sheetTitle,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_GENDER     => $object->billingInfo->gender,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_LAST_NAME  => $object->billingInfo->lastName,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_FIRST_NAME => $object->billingInfo->firstName,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_POSITION   => $object->billingInfo->position,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_PHONE      => sprintf('\'%s\'', $object->billingInfo->phone),
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_MOBILE     => sprintf('\'%s\'', $object->billingInfo->mobile),
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_COMPANY    => $object->billingInfo->company,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_EMAIL      => $object->billingInfo->email,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_STREET     => $object->billingInfo->street,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_ZIP_CODE   => $object->billingInfo->zipCode,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_CITY       => $object->billingInfo->city,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_COUNTRY    => $object->billingInfo->country,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_VAT_NUMBER => $object->billingInfo->vatNumber,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_REFERENCE  => $object->billingInfo->reference,
        ];

        foreach ($object->productBoughtViews as $productBought) {
            $data[$productBought->getUnitPriceColumnId()] = $productBought->unitPrice;
            $data[$productBought->getQuantityColumnId()]  = $productBought->quantity;
            $data[$productBought->getTotalColumnId()]     = $productBought->total;
        }

        $index = 1;
        foreach ($object->customRowsViews as $customRowView) {
            $data[$customRowView->getTitleColumnId($index)]     = $customRowView->title;
            $data[$customRowView->getUnitPriceColumnId($index)] = $customRowView->unitPrice;
            $data[$customRowView->getQuantityColumnId($index)]  = $customRowView->quantity;
            $data[$customRowView->getTotalColumnId($index)]  = $customRowView->total;

            $index++;
        }

        $output = [];

        foreach ($object->columnArray as $key => $column) {
            if (isset($data[$key])) {
                $output[$key] = $data[$key];
            } else {
                $output[$key] = null;
            }
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof OrderView && $format === 'csv';
    }
}
