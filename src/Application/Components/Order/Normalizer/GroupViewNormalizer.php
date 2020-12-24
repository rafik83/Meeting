<?php

namespace Proximum\Vimeet\Application\Components\Order\Normalizer;

use Proximum\Vimeet\Application\View\Order\GroupView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param GroupView $object
     * @param string    $format
     * @param array     $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'groupId'    => $object->groupId,
            'label'      => $object->label,
            'type'       => $object->type,
            'products'   => $this->normalizer->normalize($object->products, $format, $context),
            'customRows' => $this->normalizer->normalize($object->customRows, $format, $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupView;
    }
}
