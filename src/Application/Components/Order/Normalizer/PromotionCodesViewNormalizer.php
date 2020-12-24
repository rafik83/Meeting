<?php

namespace Proximum\Vimeet\Application\Components\Order\Normalizer;

use Proximum\Vimeet\Application\View\Order\PromotionCodesView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PromotionCodesViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param PromotionCodesView $object
     * @param string             $format
     * @param array              $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'promotionCodes' => $this->normalizer->normalize($object->promotionCodes, $format, $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PromotionCodesView;
    }
}
