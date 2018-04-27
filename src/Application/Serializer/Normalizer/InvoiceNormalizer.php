<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use Proximum\Vimeet\Domain\View\Normalizer\InvoicesNormalizerView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class InvoiceNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_EVENT_ID                = 'event_id';
    const COL_EVENT_NAME              = 'event_name';
    const COL_OWNER_ID                = 'owner_id';
    const COL_SHEET_ID                = 'sheet_id';
    const COL_SHEET_TITLE             = 'sheet_title';
    const COL_INVOICE_DATE            = 'invoice_date';
    const COL_INVOICE_NUMBER          = 'invoice_number';
    const COL_TOTAL                   = 'total';
    const COL_TOTAL_WITH_VAT          = 'total_with_vat';
    const COL_VAT_AMOUNT              = 'vat_amount';
    const COL_BALANCE                 = 'balance';
    const COL_INTRA_COMMUNITY_VAT     = 'vat_number';
    const COL_BILLING_CONTACT_COUNTRY = 'billing_contact_country';
    const COL_ANALYTIC_CODE           = 'analytic_code';
    const EXPORT_BASE_KEY             = 'admin.invoice.export.fields.';

    /**
     * @var string
     */
    protected $normalizerType = 'invoice';

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator);
    }

    /**
     * Normalizes an invoice for serialization
     *
     * {@inheritdoc}
     *
     * @param InvoicesNormalizerView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $rawInvoices        = [];
        $normalizedInvoices = [];

        foreach ($object->exportViews as $invoice) {
            $rawInvoices[] = $this->getInvoiceRawData($invoice, $object->locale);
        }

        $charset = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;

        foreach ($rawInvoices as $rawInvoice) {
            $normalizedInvoices[] = $this->normalizeInvoiceRawData($rawInvoice, $charset);
        }

        return $normalizedInvoices;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof InvoicesNormalizerView && 'csv' === $format;
    }

    /**
     * @param ExportView $invoice
     *
     * @return array Raw data about invoice
     */
    private function getInvoiceRawData(ExportView $invoice)
    {
        $rawData = [
            self::COL_EVENT_ID                => $invoice->eventId,
            self::COL_EVENT_NAME              => $invoice->eventTitle,
            self::COL_OWNER_ID                => $invoice->ownerId,
            self::COL_SHEET_ID                => $invoice->sheetId,
            self::COL_SHEET_TITLE             => $invoice->sheetTitle,
            self::COL_INVOICE_NUMBER          => $invoice->invoiceNumber,
            self::COL_INVOICE_DATE            => $invoice->invoiceDate,
            self::COL_TOTAL                   => $invoice->total,
            self::COL_TOTAL_WITH_VAT          => $invoice->totalWithVat,
            self::COL_VAT_AMOUNT              => $invoice->vatAmount,
            self::COL_BALANCE                 => $invoice->balance,
            self::COL_INTRA_COMMUNITY_VAT     => $invoice->vatNumber,
            self::COL_BILLING_CONTACT_COUNTRY => $invoice->billingInfoCountry,
            self::COL_ANALYTIC_CODE           => $invoice->analyticsCode,
        ];

        return $rawData;
    }

    /**
     * Returns an array of normalized data from an invoice's raw data
     *
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    private function normalizeInvoiceRawData($rawData, $charset = Charset::WINDOWS_1252)
    {
        $normalizedData = [];

        foreach (self::getFieldKeys() as $fieldKey) {
            $translationKey = self::EXPORT_BASE_KEY . $fieldKey;
            $input          = $rawData[$fieldKey];

            $translatedFieldname = $this->convertCharset(
                $this->translator->trans($translationKey),
                Charset::UTF_8,
                $charset
            );

            $normalizedData[$translatedFieldname] = $this->convertCharset(
                $input,
                Charset::UTF_8,
                $charset
            );
        }

        return $normalizedData;
    }

    /**
     * @return string[] Keys of common columns' headers
     */
    private static function getFieldKeys()
    {
        return [
            self::COL_EVENT_ID,
            self::COL_EVENT_NAME,
            self::COL_OWNER_ID,
            self::COL_SHEET_ID,
            self::COL_SHEET_TITLE,
            self::COL_INVOICE_NUMBER,
            self::COL_INVOICE_DATE,
            self::COL_TOTAL,
            self::COL_TOTAL_WITH_VAT,
            self::COL_VAT_AMOUNT,
            self::COL_BALANCE,
            self::COL_INTRA_COMMUNITY_VAT,
            self::COL_BILLING_CONTACT_COUNTRY,
            self::COL_ANALYTIC_CODE,
        ];
    }
}
