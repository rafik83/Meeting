<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SpotSatisfactionViewNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        /** @var SpotSatisfactionView $spotSatisfactionView */
        $spotSatisfactionView = $object;

        return [
            'spotId' => $spotSatisfactionView->id,
            'reference' => $spotSatisfactionView->reference,
            'shared' => $spotSatisfactionView->shared,
            'visio' => $spotSatisfactionView->visio,
            'satisfaction' => $spotSatisfactionView->satisfaction,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $format === 'json' && $data instanceof SpotSatisfactionView;
    }
}
