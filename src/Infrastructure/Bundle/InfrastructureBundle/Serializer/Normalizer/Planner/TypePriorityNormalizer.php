<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\TypePriorityView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TypePriorityNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'fromType' => [
                '@reference' => $object->getFromTypeReference(),
            ],
            'toType' => [
                '@reference' => $object->getToTypeReference(),
            ],
            'priority' => $object->priority,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof TypePriorityView;
    }
}
