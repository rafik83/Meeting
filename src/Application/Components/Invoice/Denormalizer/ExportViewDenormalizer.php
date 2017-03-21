<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ExportViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var ExportView $invoice */
        $invoice = $context['invoice'];

        /** @var BillingInfosView $billingInfo */
        $billingInfo = $this
            ->denormalizer
            ->denormalize($data['billingInfosView'], BillingInfosView::class, $format, $context);

        $invoice->vatNumber          = $billingInfo->vatNumber;
        $invoice->billingInfoCountry = $billingInfo->country;

        return $invoice;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === ExportView::class && isset($data['billingInfosView']);
    }
}
