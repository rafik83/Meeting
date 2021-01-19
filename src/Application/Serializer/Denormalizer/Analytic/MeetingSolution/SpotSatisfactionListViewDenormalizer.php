<?php

namespace Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SpotSatisfactionListViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $spotSatisfactionlist = new SpotSatisfactionListView();

        foreach ($data as $spotSatisfaction) {
            $spotSatisfactionlist->addSpotSatisfaction(
                $this->denormalizer->denormalize($spotSatisfaction, SpotSatisfactionView::class, $format)
            );
        }

        return $spotSatisfactionlist;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return SpotSatisfactionListView::class === $type && 'json' === $format;
    }
}
