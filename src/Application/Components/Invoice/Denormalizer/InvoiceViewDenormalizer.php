<?php

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Application\View\Invoice\InvoiceView;
use Proximum\Vimeet\Application\View\Invoice\SummaryView;
use Proximum\Vimeet\Application\View\Invoice\Vat\VatListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Type\PaymentConditions;
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
        $paymentConditions = $invoice->getSheet()->getType()->getPaymentConditions();

        if ($paymentConditions instanceof PaymentConditions) {
            $billingAddress = $paymentConditions->getBillingAddress($eventDefaultLocale);
            $bankInfo = $paymentConditions->getBankInfo($eventDefaultLocale);
            $paymentCondition = $paymentConditions->getPaymentCondition($eventDefaultLocale);
            $paymentFooter = $paymentConditions->getPaymentFooter($eventDefaultLocale);
        } else {
            $billingAddress = $event->getBillingAddress($eventDefaultLocale);
            $bankInfo = $event->getBankInfo($eventDefaultLocale);
            $paymentCondition = $event->getPaymentCondition($eventDefaultLocale);
            $paymentFooter = $event->getPaymentFooter($eventDefaultLocale);
        }

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
            $billingAddress,
            $bankInfo,
            $paymentCondition,
            $paymentFooter,
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
