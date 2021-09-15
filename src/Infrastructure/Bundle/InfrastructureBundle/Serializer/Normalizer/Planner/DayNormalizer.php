<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\Day;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DayNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            '@id'   => $object->reference,
            'id'    => $object->id,
            'day'   => $object->day,
            'month' => $object->month,
            'year'  => $object->year,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Day;
    }
}
