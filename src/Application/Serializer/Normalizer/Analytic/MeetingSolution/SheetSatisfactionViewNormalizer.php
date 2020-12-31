<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SheetSatisfactionViewNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var SheetSatisfactionView $sheetSatisfactionView */
        $sheetSatisfactionView = $object;

        return [
            'sheetId' => $sheetSatisfactionView->id,
            'sheetTitle' => $sheetSatisfactionView->title,
            'typeId' => $sheetSatisfactionView->typeId,
            'typeTitle' => $sheetSatisfactionView->typeTitle,
            'satisfaction' => $sheetSatisfactionView->satisfaction,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'json' === $format && $data instanceof SheetSatisfactionView;
    }
}
