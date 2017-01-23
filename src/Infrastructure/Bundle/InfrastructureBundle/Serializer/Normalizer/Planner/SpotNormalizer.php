<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SpotView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SpotNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [
            '@id'             => $object->reference,
            'id'              => $object->id,
            'reference'       => $object->spotReference,
            'seatCapacity'    => $object->seatCapacity,
            'meetingCapacity' => $object->meetingCapacity,
            'priority'        => $object->priority,
            'sheetList'       => [],
        ];

        if (!empty($object->sheetList)) {
            $data['sheetList'] = [
                'Sheet' => array_map(
                    function (SheetView $sheet) {
                        return  ['@reference' => $sheet->reference];
                    }, $object->sheetList
                ),
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SpotView;
    }
}
