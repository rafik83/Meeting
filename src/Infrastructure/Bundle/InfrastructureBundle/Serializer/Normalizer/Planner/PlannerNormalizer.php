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
        return [
            'dayList'          => [
                'Day' => $this->serializer->normalize($object->dayList, $format, $context),
            ],
            'slotList'         => [
                'Slot' => $this->serializer->normalize($object->slotList, $format, $context),
            ],
            'typeList'         => [
                'Type' => $this->serializer->normalize($object->typeList, $format, $context),
            ],
            'typePriorityList' => [
                'TypePriority' => $this->serializer->normalize($object->typePriorityList, $format, $context),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PlannerView;
    }
}
