<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\View\Normalizer\InvoicesNormalizerView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class InvoiceNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_EVENT_ID                = 'event_id';
    const COL_EVENT_NAME              = 'event_name';
    const COL_OWNER_ID                = 'owner_id';
    const COL_SHEET_TITLE             = 'sheet_title';
    const COL_INVOICE_NUMBER          = 'invoice_number';
    const COL_TOTAL                   = 'total';
    const COL_TOTAL_WITH_VAT          = 'total_with_vat';
    const COL_VAT_AMOUNT              = 'vat_amount';
    const COL_BALANCE                 = 'balance';
    const COL_INTRA_COMMUNITY_VAT     = 'intra_community_vat';
    const COL_BILLING_CONTACT_COUNTRY = 'billing_contact_country';
    const COL_ANALYTIC_CODE           = 'analytic_code';

    const EXPORT_BASE_KEY = 'admin.invoice.export.fields.';

    /**
     * @var string
     */
    protected $normalizerType = 'invoice';

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * @param TranslatorInterface        $translator
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param EventRepositoryInterface   $eventRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param Balance                    $balance
     */
    public function __construct(
        TranslatorInterface $translator,
        InvoiceRepositoryInterface $invoiceRepository,
        EventRepositoryInterface $eventRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        Balance $balance
    ) {
        parent::__construct($translator);
        $this->invoiceRepository = $invoiceRepository;
        $this->eventRepository   = $eventRepository;
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
        $this->balance           = $balance;
    }

    /**
     * Normalizes an event's sheets for serialization
     *
     * {@inheritdoc}
     *
     * @param InvoicesNormalizerView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $rawInvoices        = [];
        $normalizedInvoices = [];

        $events = $this->eventRepository->getEventsByAdmin($object->user);

        foreach ($events as $event) {
            $invoices = $this->invoiceRepository->getAllByEvent($event);

            foreach ($invoices as $invoice) {
                $rawInvoices[] = $this->getInvoiceRawData($invoice, $object->user->getLocale());
            }
        }

        $charset = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;

        foreach ($rawInvoices as $rawInvoice) {
            $normalizedMeetings[] = $this->normalizeInvoiceRawData($rawInvoice, $charset);
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
     * @param Invoice $invoice
     * @param string  $adminLocale
     *
     * @return array Raw data about meeting
     */
    private function getInvoiceRawData(Invoice $invoice, $adminLocale)
    {
        $sheetTitle = $this->sheetInfoGuesser->guessSheetTitle(
            $invoice->getSheet(),
            $invoice->getEvent()->getAvailableLocale($adminLocale)
        );

        $rawData = [
            self::COL_EVENT_ID       => $invoice->getEvent()->getId(),
            self::COL_EVENT_NAME     => $invoice->getEvent()->getTitle(),
            self::COL_OWNER_ID       => $invoice->getSheet()->getOwner()->getFullname(),
            self::COL_SHEET_TITLE    => $sheetTitle,
            self::COL_INVOICE_NUMBER => $invoice->getNumber(),
            self::COL_TOTAL          => $invoice->getTotal(),
            self::COL_TOTAL_WITH_VAT => $invoice->getTotalWithVat(),
            self::COL_VAT_AMOUNT     => $invoice->getVatAmount(),
            self::COL_BALANCE        => $this->balance->getBalance($invoice->getSheet()),
            //            self::COL_INTRA_COMMUNITY_VAT => $invoice->getData()->getBillingInfo()->getVatNumber(),
            //            self::COL_BILLING_CONTACT_COUNTRY => $invoice->getData()->getBillingInfo()->getAddress()->getCountry(),
            self::COL_ANALYTIC_CODE  => $invoice->getEvent()->getConfiguration()->getAnalyticsCode(),
        ];

        return $rawData;
    }

    /**
     * Returns an array of normalized data from a meeting's raw data
     *
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    private function normalizeInvoiceRawData($rawData, $charset = Charset::WINDOWS_1252)
    {
        $normalizedData = [];

        foreach (self::getCommonFieldKeys() as $fieldKey) {
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
    private static function getCommonFieldKeys()
    {
        return [
            self::COL_EVENT_ID,
            self::COL_EVENT_NAME,
            self::COL_OWNER_ID,
            self::COL_SHEET_TITLE,
            self::COL_INVOICE_NUMBER,
            self::COL_TOTAL,
            self::COL_TOTAL_WITH_VAT,
            self::COL_VAT_AMOUNT,
            self::COL_BALANCE,
            //            self::COL_INTRA_COMMUNITY_VAT,
            //            self::COL_BILLING_CONTACT_COUNTRY,
            self::COL_ANALYTIC_CODE,
        ];
    }
}
