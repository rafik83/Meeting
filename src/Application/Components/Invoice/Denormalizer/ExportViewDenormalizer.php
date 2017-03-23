<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use IntlDateFormatter;

class ExportViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * @param SheetInfoGuesserCache $sheetInfoGuesser
     * @param Balance               $balance
     */
    public function __construct(
        SheetInfoGuesserCache $sheetInfoGuesser,
        Balance $balance
    ) {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->balance          = $balance;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var Invoice $invoice */
        $invoice = $context['invoice'];

        /** @var BillingInfosView $billingInfo */
        $billingInfo = $this
            ->denormalizer
            ->denormalize($data['billingInfosView'], BillingInfosView::class, $format, $context);

        $sheetTitle = $this->sheetInfoGuesser->guessSheetTitle(
            $invoice->getSheet(),
            $invoice->getEvent()->getAvailableLocale($context['locale'])
        );

        $invoiceDate = $this->getFormattedDate(
            $context['dateFormatters'][$invoice->getEvent()->getId()],
            $invoice->getCreatedAt()
        );

        $invoiceExportView = new ExportView(
            $invoice->getEvent()->getId(),
            $invoice->getEvent()->getTitle(),
            $invoice->getSheet()->getOwner()->getId(),
            $invoice->getSheet()->getId(),
            $sheetTitle,
            $invoice->getNumber(),
            $invoiceDate,
            AmountFormatter::centsToDecimalAmount($invoice->getTotal()),
            AmountFormatter::centsToDecimalAmount($invoice->getTotalWithVat()),
            $invoice->getVatAmount(),
            AmountFormatter::centsToDecimalAmount($this->balance->getBalance($invoice->getSheet())),
            $invoice->getEvent()->getConfiguration()->getAnalyticsCode(),
            $billingInfo->vatNumber,
            $billingInfo->country
        );

        return $invoiceExportView;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === ExportView::class && isset($data['billingInfosView']);
    }

    /**
     * @param IntlDateFormatter  $dateFormatter
     * @param \DateTimeInterface $date
     *
     * @return bool|string
     */
    private function getFormattedDate(IntlDateFormatter $dateFormatter, \DateTimeInterface $date)
    {
        return $dateFormatter->format($date) ? $dateFormatter->format($date) : '';
    }
}
