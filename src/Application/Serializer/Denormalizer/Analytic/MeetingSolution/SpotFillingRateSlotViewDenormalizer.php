<?php

namespace Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateSlotView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SpotFillingRateSlotViewDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var \DateTime $begin */
        $begin = $this->denormalizer->denormalize($data['begin'], \DateTime::class, $format);

        /** @var \DateTime $end */
        $end = $this->denormalizer->denormalize($data['end'], \DateTime::class, $format);

        return new SpotFillingRateSlotView($begin, $end, $data['fillingRate']);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return SpotFillingRateSlotView::class === $type
            && 'json' === $format
            && isset($data['begin'])
            && isset($data['end'])
            && isset($data['fillingRate'])
        ;
    }
}
