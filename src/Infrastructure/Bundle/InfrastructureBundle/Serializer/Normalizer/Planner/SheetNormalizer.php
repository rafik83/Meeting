<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\SheetView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SheetNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            '@id'                      => $object->reference,
            'id'                       => $object->id,
            'planningQuantity'         => $object->planningQuantity,
            'possibleMeetingsQuantity' => $object->possibleMeetingsQuantity,
            'type'                     => [
                '@reference' => $object->getTypeReference(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SheetView;
    }
}
