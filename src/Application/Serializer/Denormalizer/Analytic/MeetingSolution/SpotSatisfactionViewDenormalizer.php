<?php

namespace Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SpotSatisfactionViewDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new SpotSatisfactionView(
            $data['spotId'],
            $data['reference'],
            $data['shared'],
            $data['visio'],
            $data['priority'] ?? null,
            $data['satisfaction']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return SpotSatisfactionView::class === $type
            && 'json' === $format
            && isset($data['spotId'])
            && isset($data['reference'])
            && isset($data['shared'])
            && isset($data['visio'])
            && isset($data['satisfaction'])
        ;
    }
}
