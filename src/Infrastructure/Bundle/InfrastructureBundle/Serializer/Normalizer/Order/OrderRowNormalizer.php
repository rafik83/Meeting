<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Order;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Order\Export\OrderView;
use Proximum\Vimeet\Application\View\Order\Export\SharedColumnsTranslationView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrderRowNormalizer implements NormalizerInterface
{
    private $charset = Charset::UTF_8;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof OrderView) {
            throw new \Exception('Invalid object');
        }

        if (isset($context['charset']) && $context['charset'] !== $this->charset) {
            $this->charset = $context['charset'];
        }

        $data = [
            SharedColumnsTranslationView::COLUMN_ORDER_ID                => $object->orderId,
            SharedColumnsTranslationView::COLUMN_ORDER_DATE              => $object->orderDate,
            SharedColumnsTranslationView::COLUMN_SHEET_ID                => $object->sheetId,
            SharedColumnsTranslationView::COLUMN_SHEET_TITLE             => $this->convertCharset($object->sheetTitle),
            SharedColumnsTranslationView::COLUMN_INVOICE_NUMBER          => $object->invoiceNumber,
            SharedColumnsTranslationView::COLUMN_INVOICE_DATE            => $object->invoiceDate,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_GENDER     => $object->billingInfo->gender,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_LAST_NAME  => $this->convertCharset($object->billingInfo->lastName),
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_FIRST_NAME => $this->convertCharset($object->billingInfo->firstName),
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_POSITION   => $this->convertCharset($object->billingInfo->position),
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_PHONE      => $this->formatPhoneNumber($object->billingInfo->phone),
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_MOBILE     => $this->formatPhoneNumber($object->billingInfo->mobile),
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_COMPANY    => $this->convertCharset($object->billingInfo->company),
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_EMAIL      => $object->billingInfo->email,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_STREET     => $this->convertCharset($object->billingInfo->street),
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_ZIP_CODE   => $object->billingInfo->zipCode,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_CITY       => $object->billingInfo->city,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_COUNTRY    => $object->billingInfo->country,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_VAT_NUMBER => $object->billingInfo->vatNumber,
            SharedColumnsTranslationView::COLUMN_BILLING_INFO_REFERENCE  => $object->billingInfo->reference,
            SharedColumnsTranslationView::COLUMN_ORDER_TOTAL_WITHOUT_VAT => $object->totalWithoutVat,
            SharedColumnsTranslationView::COLUMN_ORDER_TOTAL_VAT         => $object->totalVat,
            SharedColumnsTranslationView::COLUMN_ORDER_TOTAL_WITH_VAT    => $object->totalWithVat,
        ];

        foreach ($object->promotionCodeBoughtViews as $promotionCodeBought) {
            $data[$promotionCodeBought->getQuantityColumnId()] = $promotionCodeBought->quantity;
            $data[$promotionCodeBought->getTotalColumnId()] = $promotionCodeBought->total;
        }

        $index = 1;
        foreach ($object->customRowsViews as $customRowView) {
            $data[$customRowView->getTitleColumnId($index)] = $this->convertCharset($customRowView->title);
            $data[$customRowView->getUnitPriceColumnId($index)] = $customRowView->unitPrice;
            $data[$customRowView->getQuantityColumnId($index)] = $customRowView->quantity;
            $data[$customRowView->getTotalColumnId($index)] = $customRowView->total;

            ++$index;
        }

        foreach ($object->productBoughtViews as $productBought) {
            $data[$productBought->getUnitPriceColumnId()] = $productBought->unitPrice;
            $data[$productBought->getQuantityColumnId()] = $productBought->quantity;
            $data[$productBought->getTotalColumnId()] = $productBought->total;
        }

        $output = [];

        foreach ($object->columnArray as $key => $column) {
            $output[$key] = $data[$key] ?? null;
        }

        return $output;
    }

    /**
     * @param string|null $phoneNumber
     *
     * @return null|string
     */
    private function formatPhoneNumber($phoneNumber)
    {
        return null !== $phoneNumber ? sprintf('\'%s\'', $phoneNumber) : null;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    private function convertCharset($input)
    {
        if (Charset::UTF_8 !== $this->charset) {
            return iconv(Charset::UTF_8, Charset::WINDOWS_1252 . '//TRANSLIT', $input);
        }

        return $input;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof OrderView && 'csv' === $format;
    }
}
