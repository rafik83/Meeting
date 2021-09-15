<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Application\View\Planner\SpotView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SpotNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [
            '@id'                => $object->reference,
            'id'                 => $object->id,
            'isVisio'            => $object->isVisio ? 'true' : 'false',
            'reference'          => $object->spotReference,
            'seatCapacity'       => $object->seatCapacity,
            'meetingCapacity'    => $object->meetingCapacity,
            'priority'           => $object->priority,
            'sheetList'          => [],
            'unavailabilityList' => [],
        ];

        if (!empty($object->sheetList)) {
            $data['sheetList'] = [
                'Sheet' => array_map(
                    function (SheetView $sheet) {
                        return  ['@reference' => $sheet->reference];
                    }, $object->sheetList
                ),
            ];
        }

        if (!empty($object->unavailabilityList)) {
            $data['unavailabilityList'] = [
                'Slot' => array_map(
                    function (SlotView $slotView) {
                        return  ['@reference' => $slotView->reference];
                    }, $object->unavailabilityList
                ),
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SpotView;
    }
}
