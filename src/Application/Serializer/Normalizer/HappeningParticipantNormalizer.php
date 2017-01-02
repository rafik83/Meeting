<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Domain\Model\Happening;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HappeningParticipantNormalizer implements NormalizerInterface
{
    const COL_HAPPENING_ID          = 'happening_id';
    const COL_HAPPENING_NAME        = 'happening_name';
    const COL_HAPPENING_DAY         = 'happening_day';
    const COL_HAPPENING_BEGIN_HOUR  = 'happening_begin_hour';
    const COL_HAPPENING_END_HOUR    = 'happening_end_hour';
    const COL_SHEET_ID              = 'sheet_id';
    const COL_SHEET_NAME            = 'sheet_name';
    const COL_PARTICIPANT_ID        = 'participant_id';
    const COL_PARTICIPANT_EMAIL     = 'participant_id';
    const COL_PARTICIPANT_FIRSTNAME = 'participant_firstname';
    const COL_PARTICIPANT_LASTNAME  = 'participant_lastname';
    const COL_PARTICIPANT_POSITION  = 'participant_position';
    const COL_QUESTION              = 'question_id';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        // TODO: Implement normalize() method.
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Happening && 'csv' === $format;
    }
}
