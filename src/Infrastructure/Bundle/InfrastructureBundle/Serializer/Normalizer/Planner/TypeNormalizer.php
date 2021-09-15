<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\TypeView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TypeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            '@id'   => $object->reference,
            'id'    => $object->id,
            'title' => $object->title,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof TypeView;
    }
}
