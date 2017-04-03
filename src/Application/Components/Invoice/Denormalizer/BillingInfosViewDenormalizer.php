<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\Exception\Invoice\MissingArgumentBillingInfosException;
use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BillingInfosViewDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!isset($context['billingInfosViewOfSheet'])
            || !$context['billingInfosViewOfSheet'] instanceof BillingInfosView
        ) {
            throw new MissingArgumentBillingInfosException();
        }

        /** @var BillingInfosView $bilingInfosView */
        $bilingInfosView = $context['billingInfosViewOfSheet'];

        $bilingInfosView->country   = $data['country'];
        $bilingInfosView->vatNumber = isset($data['vatNumber']) ? $data['vatNumber'] : null;

        return $bilingInfosView;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === BillingInfosView::class && isset($data['country']);
    }
}
