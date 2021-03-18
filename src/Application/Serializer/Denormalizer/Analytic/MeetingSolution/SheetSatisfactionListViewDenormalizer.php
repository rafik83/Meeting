<?php

namespace Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SheetSatisfactionListViewDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $sheetSatisfactionlist = new SheetSatisfactionListView();

        foreach ($data as $sheetSatisfaction) {
            if (null === $sheetSatisfaction['sheetTitle']) {
                $sheetSatisfaction['sheetTitle'] = 'N/C';
            }

            $sheetSatisfactionlist->addSheetSatisfaction(
                $this->denormalizer->denormalize($sheetSatisfaction, SheetSatisfactionView::class, $format)
            );
        }

        return $sheetSatisfactionlist;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return SheetSatisfactionListView::class === $type && 'json' === $format;
    }
}
