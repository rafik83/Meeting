<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Planner;

use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ParticipantNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [
            '@id'                => $object->reference,
            'id'                 => $object->userId,
            'fullName'           => $object->fullName,
            'unavailabilityList' => [],
        ];

        if (!empty($object->unavailabilityList)) {
            $data['unavailabilityList'] = [
                'Slot' => array_map(function (SlotView $slot) {
                    return ['@reference' => $slot->reference];
                }, $object->unavailabilityList),
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ParticipantView;
    }
}
