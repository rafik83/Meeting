<?php

namespace Proximum\Vimeet\Application\Components\Order\Normalizer;

use Proximum\Vimeet\Application\View\Order\GroupsView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupsViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param GroupsView $object
     * @param string     $format
     * @param array      $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'groups' => $this->normalizer->normalize($object->groups, $format, $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupsView;
    }
}
