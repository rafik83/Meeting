<?php

namespace Proximum\Vimeet\Application\Components\Order\Normalizer;

use Proximum\Vimeet\Application\View\Order\PromotionCodeView;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PromotionCodeViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param PromotionCodeView $object
     * @param string            $format
     * @param array             $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'label'                    => $object->label,
            'description'              => $object->description,
            'quantity'                 => $object->quantity,
            'total'                    => AmountFormatter::decimalToCentsAmount($object->total),
            'promotionProductRowViews' => $this->normalizer->normalize($object->promotionProductRowViews, $format, $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PromotionCodeView;
    }
}
