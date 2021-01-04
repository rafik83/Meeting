<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SpotSatisfactionViewNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var SpotSatisfactionView $spotSatisfactionView */
        $spotSatisfactionView = $object;

        return [
            'spotId' => $spotSatisfactionView->id,
            'reference' => $spotSatisfactionView->reference,
            'shared' => $spotSatisfactionView->shared,
            'visio' => $spotSatisfactionView->visio,
            'satisfaction' => $spotSatisfactionView->satisfaction,
            'priority' => $spotSatisfactionView->priority,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'json' === $format && $data instanceof SpotSatisfactionView;
    }
}
