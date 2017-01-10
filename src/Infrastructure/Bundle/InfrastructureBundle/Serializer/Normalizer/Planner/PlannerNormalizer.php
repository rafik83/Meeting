<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\PlannerView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlannerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [
            'dayList'          => [],
            'slotList'         => [],
            'typeList'         => [],
            'typePriorityList' => [],
            'sheetList'        => [],
            'participantList'  => [],
            'meetingList'      => [],
            'spotList'         => [],
        ];

        if (!empty($object->dayList)) {
            $data['dayList'] = [
                'Day' => $this->normalizer->normalize($object->dayList, $format, $context),
            ];
        }

        if (!empty($object->slotList)) {
            $data['slotList'] = [
                'Slot' => $this->normalizer->normalize($object->slotList, $format, $context),
            ];
        }

        if (!empty($object->typeList)) {
            $data['typeList'] = [
                'Type' => $this->normalizer->normalize($object->typeList, $format, $context),
            ];
        }

        if (!empty($object->typePriorityList)) {
            $data['typePriorityList'] = [
                'TypePriority' => $this->normalizer->normalize($object->typePriorityList, $format, $context),
            ];
        }

        if (!empty($object->sheetList)) {
            $data['sheetList'] = [
                'Sheet' => $this->normalizer->normalize($object->sheetList, $format, $context),
            ];
        }

        if (!empty($object->participantList)) {
            $data['participantList'] = [
                'Participant' => $this->normalizer->normalize($object->participantList, $format, $context),
            ];
        }

        if (!empty($object->meetingList)) {
            $data['meetingList'] = [
                'Meeting' => $this->normalizer->normalize($object->meetingList, $format, $context),
            ];
        }

        if (!empty($object->spotList)) {
            $data['spotList'] = [
                'Spot' => $this->normalizer->normalize($object->spotList, $format, $context),
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PlannerView;
    }

    /**
     * {@inheritdoc}
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }
}
