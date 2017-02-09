<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\MeetingView;
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MeetingNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            '@id'             => $object->reference,
            'id'              => $object->id,
            'isVisio'         => $object->isVisio,
            'sheetList'       => [
                'Sheet' => array_map(function (SheetView $sheet) {
                    return ['@reference' => $sheet->reference];
                }, $object->sheetList),
            ],
            'participantList' => [
                'Participant' => array_map(function (ParticipantView $participant) {
                    return ['@reference' => $participant->reference];
                }, $object->participantList),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MeetingView;
    }
}
