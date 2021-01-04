<?php

namespace Proximum\Vimeet\Application\Components\Order\Normalizer;

use Proximum\Vimeet\Application\View\Order\SummaryView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SummaryViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param SummaryView $object
     * @param string      $format
     * @param array       $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'groups'         => $this->normalizer->normalize($object->groups, $format, $context),
            'promotionCodes' => $this->normalizer->normalize($object->promotionCodes, $format, $context),
            'vatMode'        => $object->vatMode,
            'currency'       => $object->currency,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SummaryView;
    }
}
