<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution;


use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SheetSatisfactionListViewDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $sheetSatisfactionlist = new SheetSatisfactionListView();

        foreach ($data as $sheetSatisfaction) {
            $sheetSatisfactionlist->addSheetSatisfaction(
                $this->denormalizer->denormalize($sheetSatisfaction, SheetSatisfactionView::class, $format)
            );
        }

        return $sheetSatisfactionlist;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === SheetSatisfactionListView::class && $format === 'json';
    }
}
