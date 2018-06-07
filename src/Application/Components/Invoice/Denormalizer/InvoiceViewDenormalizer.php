<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Application\View\Invoice\InvoiceView;
use Proximum\Vimeet\Application\View\Invoice\SummaryView;
use Proximum\Vimeet\Application\View\Invoice\Vat\VatListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class InvoiceViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param array $context should contain billingInfosViewOfSheet to be passed to the billingInfosViewDenormalizer
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var Invoice $invoice */
        $invoice = $context['invoice'];

        /** @var Event $event */
        $event = $invoice->getEvent();

        $eventDefaultLocale = $event->getFallback();

        return new InvoiceView(
            $invoice->getNumber(),
            $invoice->isVatApplicable(),
            $invoice->getVatMode(),
            $invoice->getVatRate(),
            $invoice->getTotal(),
            $invoice->getTotalWithVat(),
            $invoice->getVatAmount(),
            $invoice->getCurrency(),
            $event->getTitle(),
            $event->getInvoiceLogo(),
            $invoice->getCreatedAt(),
            $eventDefaultLocale,
            $event->getTimeZone(),
            $event->getBillingAddress($eventDefaultLocale),
            $event->getBankInfo($eventDefaultLocale),
            $event->getPaymentCondition($eventDefaultLocale),
            $event->getPaymentFooter($eventDefaultLocale),
            $this->denormalizer->denormalize($data['summaryView'], SummaryView::class, $format, $context),
            $this->denormalizer->denormalize($data['billingInfosView'], BillingInfosView::class, $format, $context),
            isset($data['vatListView'])
                ? $this->denormalizer->denormalize($data['vatListView'], VatListView::class, $format, $context)
                : null,
            $data['amountRemainToPay']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return InvoiceView::class === $type
            && isset($data['summaryView'])
            && isset($data['billingInfosView'])
            && isset($data['amountRemainToPay']);
    }
}
