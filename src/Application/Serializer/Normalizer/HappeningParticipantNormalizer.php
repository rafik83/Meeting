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
