<?php

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\PromotionProductRowView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PromotionProductRowViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new PromotionProductRowView(
            $data['product'],
            $data['promotionType'],
            $data['discountValue'],
            $data['quantity']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return PromotionProductRowView::class === $type
            && isset($data['product'])
            && isset($data['promotionType'])
            && isset($data['discountValue'])
            && isset($data['quantity']);
    }
}
