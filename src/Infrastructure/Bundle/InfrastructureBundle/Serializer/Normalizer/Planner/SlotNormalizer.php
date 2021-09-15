<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\SlotView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SlotNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            '@id'    => $object->reference,
            'id'     => $object->id,
            'index'  => $object->index,
            'hour'   => $object->hour,
            'minute' => $object->minute,
            'day'    => [
                '@reference' => $object->day->reference,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SlotView;
    }
}
