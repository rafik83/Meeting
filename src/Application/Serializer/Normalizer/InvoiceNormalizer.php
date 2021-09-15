<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use Proximum\Vimeet\Domain\View\Normalizer\InvoicesNormalizerView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class InvoiceNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    private const COL_EVENT_ID = 'event_id';
    private const COL_EVENT_NAME = 'event_name';
    private const COL_OWNER_ID = 'owner_id';
    private const COL_SHEET_ID = 'sheet_id';
    private const COL_SHEET_TITLE = 'sheet_title';
    private const COL_INVOICE_DATE = 'invoice_date';
    private const COL_INVOICE_NUMBER = 'invoice_number';
    private const COL_TOTAL_FOR_VAT_RATE = 'total_for_vat_rate';
    private const COL_TOTAL_WITH_VAT_FOR_VAT_RATE  = 'total_with_vat_for_vat_rate';
    private const COL_VAT_AMOUNT_FOR_VAT_RATE  = 'vat_amount_for_vat_rate';
    private const COL_BALANCE = 'balance';
    private const COL_INTRA_COMMUNITY_VAT = 'vat_number';
    private const COL_BILLING_CONTACT_COUNTRY = 'billing_contact_country';
    private const COL_ANALYTIC_CODE = 'analytic_code';
    private const EXPORT_BASE_KEY = 'admin.invoice.export.fields.';

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
        $normalizedInvoices = [];
        $charset = $context['charset'] ?? Charset::WINDOWS_1252;

        foreach ($object->exportViews as $invoice) {
            $normalizedInvoices[] = $this->getInvoiceData($invoice, $charset, $object->locale);
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

    private function getInvoiceData(ExportView $exportView, string $charset, string $locale): array
    {
        $rawData = [
            $this->translateKey(self::COL_EVENT_ID, $charset, $locale) => $exportView->eventId,
            $this->translateKey(self::COL_EVENT_NAME, $charset, $locale) => $this->convertInput($exportView->eventTitle, $charset),
            $this->translateKey(self::COL_OWNER_ID, $charset, $locale) => $exportView->ownerId,
            $this->translateKey(self::COL_SHEET_ID, $charset, $locale) => $exportView->sheetId,
            $this->translateKey(self::COL_SHEET_TITLE, $charset, $locale) => $this->convertInput($exportView->sheetTitle, $charset),
            $this->translateKey(self::COL_INVOICE_NUMBER, $charset, $locale) => $this->convertInput($exportView->invoiceNumber, $charset),
            $this->translateKey(self::COL_INVOICE_DATE, $charset, $locale) => $exportView->invoiceDate,
            $this->translateKey(self::COL_INTRA_COMMUNITY_VAT, $charset, $locale) => $this->convertInput($exportView->vatNumber, $charset),
            $this->translateKey(self::COL_BILLING_CONTACT_COUNTRY, $charset, $locale) => $exportView->billingInfoCountry,
            $this->translateKey(self::COL_ANALYTIC_CODE, $charset, $locale) => $this->convertInput($exportView->analyticsCode, $charset),
            $this->translateKey(self::COL_BALANCE, $charset, $locale) => AmountFormatter::centsToDecimalAmount($exportView->balance),
        ];

        if (null === $exportView->vatListView || empty($exportView->vatListView->vatViews)) {
            $this->setDataByVat(
                $rawData,
                $charset,
                $locale,
                $exportView->vatRate,
                $exportView->total,
                $exportView->vatAmount,
                $exportView->totalWithVat
            );

            return $rawData;
        }

        foreach ($exportView->vatListView->vatViews as $vatView) {
            $this->setDataByVat(
                $rawData,
                $charset,
                $locale,
                $vatView->vatRate,
                $vatView->total,
                $vatView->totalVat,
                $vatView->total + $vatView->totalVat
            );
        }

        return $rawData;
    }

    private function setDataByVat(
        array &$rawData,
        string $charset,
        string $locale,
        float $vatRate,
        int $total,
        int $vatAmount,
        int $totalWithVat
    ): void {
        $parameters = ['%vatRate%' => $vatRate];

        $colTotalForVatRate = $this->translateKey(self::COL_TOTAL_FOR_VAT_RATE, $charset, $locale, $parameters);
        $rawData[$colTotalForVatRate] = AmountFormatter::centsToDecimalAmount($total);

        $colVatAmountForVatRate = $this->translateKey(self::COL_VAT_AMOUNT_FOR_VAT_RATE, $charset, $locale, $parameters);
        $rawData[$colVatAmountForVatRate] = AmountFormatter::centsToDecimalAmount($vatAmount);

        $colTotalWithVatForVatRate = $this->translateKey(self::COL_TOTAL_WITH_VAT_FOR_VAT_RATE, $charset, $locale, $parameters);
        $rawData[$colTotalWithVatForVatRate] = AmountFormatter::centsToDecimalAmount($totalWithVat);
    }

    private function convertInput(?string $input, string $charset): ?string
    {
        return $this->convertCharset($input, Charset::UTF_8, $charset);
    }

    private function translateKey(string $fieldKey, string $charset, string $locale, array $parameters = []): string
    {
        return $this->convertInput(
            $this->translator->trans(self::EXPORT_BASE_KEY . $fieldKey, $parameters, 'messages', $locale),
            $charset
        );
    }
}
