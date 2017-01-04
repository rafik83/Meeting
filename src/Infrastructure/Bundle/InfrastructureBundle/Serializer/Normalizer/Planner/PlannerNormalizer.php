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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class PlannerNormalizer implements NormalizerInterface
{
    use SerializerAwareTrait;

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
                'Day' => $this->serializer->normalize($object->dayList, $format, $context),
            ];
        }

        if (!empty($object->slotList)) {
            $data['slotList'] = [
                'Slot' => $this->serializer->normalize($object->slotList, $format, $context),
            ];
        }

        if (!empty($object->typeList)) {
            $data['typeList'] = [
                'Type' => $this->serializer->normalize($object->typeList, $format, $context),
            ];
        }

        if (!empty($object->typePriorityList)) {
            $data['typePriorityList'] = [
                'TypePriority' => $this->serializer->normalize($object->typePriorityList, $format, $context),
            ];
        }

        if (!empty($object->sheetList)) {
            $data['sheetList'] = [
                'Sheet' => $this->serializer->normalize($object->sheetList, $format, $context),
            ];
        }

        if (!empty($object->participantList)) {
            $data['participantList'] = [
                'Participant' => $this->serializer->normalize($object->participantList, $format, $context),
            ];
        }

        if (!empty($object->meetingList)) {
            $data['meetingList'] = [
                'Meeting' => $this->serializer->normalize($object->meetingList, $format, $context),
            ];
        }

        if (!empty($object->spotList)) {
            $data['spotList'] = [
                'Spot' => $this->serializer->normalize($object->spotList, $format, $context),
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
}
