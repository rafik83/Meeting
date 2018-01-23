<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SheetSatisfactionViewNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        /** @var SheetSatisfactionView $sheetSatisfactionView */
        $sheetSatisfactionView = $object;

        return [
            'sheetId' => $sheetSatisfactionView->id,
            'sheetTitle' => $sheetSatisfactionView->title,
            'typeId' => $sheetSatisfactionView->typeId,
            'typeTitle' => $sheetSatisfactionView->typeTitle,
            'satisfaction' => $sheetSatisfactionView->satisfaction
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $format === 'json' && $data instanceof SheetSatisfactionView;
    }
}
