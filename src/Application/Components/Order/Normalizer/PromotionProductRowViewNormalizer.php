<?php

namespace Proximum\Vimeet\Application\Components\Order\Normalizer;

use Proximum\Vimeet\Application\View\Package\Summary\PromotionProductRowView;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PromotionProductRowViewNormalizer implements NormalizerInterface
{
    /**
     * @param PromotionProductRowView $object
     * @param string                  $format
     * @param array                   $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'product'       => $object->product,
            'promotionType' => $object->promotionType,
            'discountValue' => Promotion::isTypeValueOff($object->promotionType)
                ? AmountFormatter::decimalToCentsAmount($object->discountValue)
                : $object->discountValue,
            'quantity'      => $object->quantity,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PromotionProductRowView;
    }
}
