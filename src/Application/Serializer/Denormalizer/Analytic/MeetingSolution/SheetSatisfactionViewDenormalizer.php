<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SheetSatisfactionViewDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === SheetSatisfactionView::class
            && $format === 'json'
            && isset($data['sheetId'])
            && isset($data['sheetTitle'])
            && isset($data['typeId'])
            && isset($data['typeTitle'])
            && isset($data['satisfaction'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return new SheetSatisfactionView(
            $data['sheetId'],
            $data['sheetTitle'],
            $data['typeId'],
            $data['typeTitle'],
            $data['satisfaction']
        );
    }
}
