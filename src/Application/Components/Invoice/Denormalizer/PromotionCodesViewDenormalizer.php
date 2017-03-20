<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\PromotionCodesView;
use Proximum\Vimeet\Application\View\Invoice\PromotionCodeView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PromotionCodesViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $promotionCodeViews = [];

        foreach ($data['promotionCodes'] as $promotionCode) {
            $promotionCodeViews[] = $this->denormalizer->denormalize(
                $promotionCode,
                PromotionCodeView::class,
                $format,
                $context
            );
        }

        return new PromotionCodesView($promotionCodeViews);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === PromotionCodesView::class
            && isset($data['promotionCodes'])
            && is_array($data['promotionCodes']);
    }
}
