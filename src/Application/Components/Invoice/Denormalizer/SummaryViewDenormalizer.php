<?php

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\GroupsView;
use Proximum\Vimeet\Application\View\Invoice\PromotionCodesView;
use Proximum\Vimeet\Application\View\Invoice\SummaryView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SummaryViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new SummaryView(
            $this->denormalizer->denormalize($data['groups'], GroupsView::class, $format, $context),
            $this->denormalizer->denormalize($data['promotionCodes'], PromotionCodesView::class, $format, $context),
            $data['vatMode'],
            $data['currency']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return SummaryView::class === $type
            && isset($data['groups'])
            && isset($data['promotionCodes'])
            && isset($data['vatMode'])
            && isset($data['currency']);
    }
}
