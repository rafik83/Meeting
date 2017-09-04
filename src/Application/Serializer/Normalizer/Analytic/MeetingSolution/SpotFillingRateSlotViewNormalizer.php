<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateSlotView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SpotFillingRateSlotViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var SpotFillingRateSlotView $spotFillingRateSlotView */
        $spotFillingRateSlotView = $object;

        return [
            'begin' => $this->normalizer->normalize($spotFillingRateSlotView->begin, $format),
            'end' => $this->normalizer->normalize($spotFillingRateSlotView->end, $format),
            'fillingRate' => $spotFillingRateSlotView->fillingRate,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $format === 'json' && $data instanceof SpotFillingRateSlotView;
    }
}
