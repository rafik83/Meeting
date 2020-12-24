<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SpotFillingRateDayViewNormalizer implements NormalizerAwareInterface, NormalizerInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var SpotFillingRateDayView $spotFillingRateDayView */
        $spotFillingRateDayView = $object;

        return [
            'day' => $this->normalizer->normalize($spotFillingRateDayView->date, $format),
            'timeZone' => $spotFillingRateDayView->timeZone,
            'slotsFillingRate' => $this->normalizer->normalize(
                $spotFillingRateDayView->slotsFillingRate,
                $format,
                $context
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'json' === $format && $data instanceof SpotFillingRateDayView;
    }
}
