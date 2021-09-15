<?php

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\PromotionCodeView;
use Proximum\Vimeet\Application\View\Invoice\PromotionProductRowView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PromotionCodeViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $promotionProductRowViews = [];

        foreach ($data['promotionProductRowViews'] as $promotionProductRowView) {
            $promotionProductRowViews[] = $this->denormalizer->denormalize(
                $promotionProductRowView,
                PromotionProductRowView::class,
                $format,
                $context
            );
        }

        return new PromotionCodeView(
            $data['label'],
            $data['description'],
            $data['total'],
            $data['quantity'],
            $promotionProductRowViews
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return PromotionCodeView::class === $type
            && isset($data['label'])
            && isset($data['description'])
            && isset($data['total'])
            && isset($data['quantity'])
            && isset($data['promotionProductRowViews'])
            && is_array($data['promotionProductRowViews']);
    }
}
